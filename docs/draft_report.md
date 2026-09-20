# Recipe Box: Design, Implementation and Evaluation

## 1. Introduction and Requirements

### 1.1 Project Background, Objectives and Scope

Recipe Box is a dynamic web application developed for the CSCK543 group assignment. It responds to a local celebrity chef’s requirement for a user-friendly service through which visitors can discover recipes and registered users can maintain a personal collection of favourites. The project aims to combine flexible recipe discovery with clear recipe information and an interface usable across different devices and interaction methods.

The principal user journey involves searching or filtering the collection, comparing results, opening a recipe and reviewing its ingredients and preparation steps. Registration enables users to save favourites and retrieve them from their account page. Figure 1.1 illustrates the entry point to this journey: visitors can search directly, browse the complete collection or explore recipes by course.

![Recipe Box home page with keyword search, a browse-all link and course categories.](images/xampp/01-home.png)

**Figure 1.1 — Recipe Box home page and recipe-discovery entry points.** Source: project screenshot captured on 19 September 2026 during the documented macOS/XAMPP test session ([source evidence](xampp.md#7-evidence)).

The assignment specifies PHP-generated HTML5, CSS3 and JavaScript, persistent storage in MySQL, and operation through Apache using XAMPP, with assessment on Windows in a recent version of Google Chrome. Front-end frameworks must not generate the page markup, and both client-side and server-side validation are required. These constraints inform the architecture and evaluation.

The required sample collection contains at least five recipes from the supplied BBC Food list; Recipe Box includes all eight. Account settings, password reset and a read-only JSON search API extend the core scope. The API exposes recipe-search results using shared search logic, while the main browsing journey remains server-rendered. These additions are distinguished from the assignment’s minimum requirements.

### 1.2 Functional Requirements

The functional requirements define the behaviours needed to complete the main user journeys.

| ID | Requirement |
| --- | --- |
| FR1 | Allow users to register, log in and log out, storing their name, email and authentication data appropriately. |
| FR2 | Display recipes with categories, ingredients and quantities, ordered preparation steps and an amount of time for each step. |
| FR3 | Support flexible searching and combined filtering by keywords, ingredients, categories, dietary labels, cuisine, preparation/cooking time, servings and ratings. |
| FR4 | Support alternative result orderings, including alphabetical order, total time, rating and number of steps. |
| FR5 | Allow authenticated users to save and remove favourites and retrieve their saved recipes from their account page. |
| FR6 | Allow authenticated users to submit and update recipe ratings, supporting rating-based discovery. |
| FR7 | Validate relevant form input on both client and server, providing understandable feedback when input is invalid. |

Figure 1.2 illustrates the interface supporting FR3 and FR4. It combines a keyword field and sorting selector with filters for course, dietary requirements, cuisine, ingredient, time, servings and rating. The screenshot establishes the available controls; their functional correctness is evaluated separately in Section 4.

![Recipe search interface showing the pizza keyword, alphabetical sorting and expanded recipe filters.](images/xampp/02-search-results.png)

**Figure 1.2 — Search, filtering and sorting controls.** Source: project screenshot captured on 19 September 2026 during the documented macOS/XAMPP test session ([source evidence](xampp.md#7-evidence)).

The brief suggests ratings as a possible enhancement; the project includes them within its implemented scope. Successful functional delivery requires these features to work together throughout the user journey, including invalid-input and unauthorised-access cases.

### 1.3 Non-functional Requirements

Non-functional requirements define the quality of the service and the conditions under which its features should operate.

| Quality area | Requirement and evaluation approach |
| --- | --- |
| Accessibility and usability | Support keyboard and screen-reader interaction, visible focus, labelled controls, readable contrast and understandable feedback. Evaluate through automated checks and task-based interaction testing. |
| Responsiveness and compatibility | Keep content and controls usable on mobile and desktop layouts, and verify operation in the specified Windows, Apache/XAMPP and Chrome environment. |
| Reliability and maintainability | Preserve data integrity and existing behaviour through modular implementation, unit and feature tests, end-to-end journeys and automated continuous integration checks. |
| Performance and resilience | Keep page loads and searches responsive, control database-query and asset costs, and evaluate behaviour under expected load, overload and recovery. |
| Security | Protect authentication, sessions and user-specific operations; validate inputs and mitigate injection, cross-site scripting, cross-site request forgery and abusive requests. |
| Privacy and ethics | Limit personal-data collection to justified purposes, explain its use, restrict access and support appropriate deletion. Use fictional test accounts and acknowledge recipe sources. |
| Resource efficiency | Limit unnecessary transfers and processing through appropriately sized assets, compression, caching and efficient queries. |

The repository’s [performance criteria](performance.md#1-the-criteria) and [load-test specification](load-testing.md#1-test-set-up) propose measurable targets, including a Lighthouse Performance score of at least 90, API response time at the 95th percentile of no more than 500 ms at 20 concurrent users, and an error rate below 1%. These remain proposed project acceptance criteria pending group agreement. Section 4 evaluates the evidence and limitations; detailed results belong in Appendix B.

The [current verification record](status.md) distinguishes tests completed on macOS/XAMPP from the outstanding Windows validation. The documented XAMPP tests use MariaDB; this evidence should not be presented as verification of the brief’s specified MySQL environment.

<!-- Draft scope: Section 1 only. Figure numbering belongs to this report, independently of the source documents. Agreed report appendices: A Database Details; B Detailed Test Results; C Installation and Configuration. -->
