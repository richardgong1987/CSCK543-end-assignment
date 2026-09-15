// k6 load and stress test for GET /api/recipes (proposal §7.3).
//
// Run only against a local copy, never a shared or public server, and raise the API rate
// limit first, or the run measures the limiter rather than the application:
//
//   API_RECIPE_SEARCH_PER_MINUTE=1000000 php artisan serve
//   k6 run -e TEST_TYPE=load tests/load/recipe-search-api.js
//   k6 run -e TEST_TYPE=stress tests/load/recipe-search-api.js
//
// Record, for each run: requests per second (http_reqs), average and p95 response time
// (http_req_duration), error rate (http_req_failed), the highest VU count before p95 or
// errors cross the thresholds, and whether the recovery stage returns to normal.

import http from 'k6/http';
import { check } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

// A spread of real searches, so the run is not one cached query repeated.
const SEARCHES = [
    '',
    'q=pizza',
    'q=chicken&sort=rating',
    'sort=quickest',
    'max_minutes=60&sort=title',
    'min_rating=4&sort=title_desc',
    'category[]=main&sort=newest',
];

const SCENARIOS = {
    // Expected traffic for a demonstration: a steady handful of concurrent users.
    load: {
        executor: 'constant-vus',
        vus: 20,
        duration: '2m',
    },
    // Beyond expected traffic, then back down to see whether the app recovers.
    stress: {
        executor: 'ramping-vus',
        startVUs: 0,
        stages: [
            { duration: '1m', target: 50 },
            { duration: '1m', target: 100 },
            { duration: '1m', target: 200 },
            { duration: '1m', target: 400 },
            { duration: '1m', target: 0 },
        ],
    },
};

const testType = __ENV.TEST_TYPE || 'load';

export const options = {
    scenarios: { [testType]: SCENARIOS[testType] },
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<500'],
    },
};

export default function () {
    const search = SEARCHES[Math.floor(Math.random() * SEARCHES.length)];
    const response = http.get(`${BASE_URL}/api/recipes?${search}`, {
        headers: { Accept: 'application/json' },
        tags: { name: 'GET /api/recipes' },
    });

    check(response, {
        'status is 200': (r) => r.status === 200,
        'body has a data array': (r) => Array.isArray(r.json('data')),
    });
}
