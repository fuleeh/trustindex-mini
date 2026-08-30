# Trustindex Mini-Application — Architecture

## Purpose

This document explains the design of the Trustindex test application using a system-design progression: requirements, core models, interface design, data flow, high-level design, and focused deep dives.

The initial system is a production-minded modular monolith proportionate to a four-to-six-hour exercise. Scalability options are reasoned about without prematurely deploying distributed components.

## 1. Requirements

### Functional

1. Submit a company name, rating, review text, and author email.
2. Require every field, constrain rating to an integer from 1–5, and validate email format.
3. Persist valid submissions and show `Köszönjük a véleményed!`.
4. Publicly list company, stars, truncated text, and date.
5. Show one review on a separate detail route.
6. Show review count and average rating per company, ordered by average descending.
7. Search reviews by company name.
8. Reject obvious automated or abusive submissions.

### Non-functional

- Symfony 7.4, PHP 8.2+, Doctrine ORM with attributes and generated migrations.
- PostgreSQL persistence and Docker-first development; host PHP is unnecessary.
- Twig base layout, responsive styling, Forms, Validator, CSRF, and output escaping.
- PHPUnit functional coverage plus explicit average-calculation and sorting coverage.
- Symfony coding standards, strict types, PHP-CS-Fixer, and PHPStan level 8.
- Clear setup, decision, scaling, and actual work-time documentation.
- Maintainable design that can evolve when measured requirements justify it.

### Assumptions

- Reviews are public; author email is private operational data and is not rendered.
- Visitors are anonymous and company identity is initially the submitted name.
- Spam is rejected before persistence; there is no moderation queue or status.
- Server-rendered HTML is the interface; a public JSON API is not required.
- Development uses PostgreSQL. Tests should use an isolated PostgreSQL database to avoid aggregate-query dialect differences.

### Non-goals

Authentication, editing/deletion, replies, external review imports, moderation administration, company ownership verification, distributed services, queues, caches, and search clusters.

## 2. Core models

### Review

The only persisted entity required by the assignment:

| Field | Type | Notes |
| --- | --- | --- |
| `id` | integer | Generated primary key |
| `companyName` | string(255) | Submitted display name |
| `rating` | integer | Inclusive range 1–5 |
| `reviewText` | text | Public content |
| `authorEmail` | string(255) | Stored, never publicly rendered |
| `createdAt` | immutable datetime | Set automatically |
| `updatedAt` | immutable datetime | Set and updated automatically |

### ReviewInputDto

The form boundary for untrusted input. It contains the four review values and an unmapped honeypot. Validator constraints live here so invalid input cannot mutate a managed entity. The DTO improves separation; security still comes from validation, CSRF, escaping, rate limiting, and careful data exposure.

### CompanyStats

A non-persisted typed read model containing company name, review count, and average rating. It avoids weakly typed aggregate arrays across repository, controller, and template boundaries.

### Why no Company entity?

The requirements store company name on `Review`. A separate entity would introduce undefined identity, creation, ownership, and alias rules. That future normalization is covered below rather than guessed now.

## 3. HTTP interface

| Method | Route | Input | Result |
| --- | --- | --- | --- |
| `GET` | `/` | None | Review list and form |
| `POST` | `/reviews` | Form body + CSRF | Persist and redirect |
| `GET` | `/reviews/{id}` | Positive ID | Review detail or 404 |
| `GET` | `/companies` | None | Ordered aggregates |
| `GET` | `/search?q=...` | Name query | Matching reviews |

- Submission uses POST/Redirect/GET.
- Invalid input re-renders field errors without persistence.
- Spam warnings do not reveal the matching rule.
- Rate limiting communicates HTTP 429 semantics where practical.
- Search is length-limited and parameterized.
- Twig auto-escaping stays enabled; email is never rendered.

## 4. Data flow

### Write

```text
Browser -> Nginx -> Controller -> ReviewType + CSRF
        -> ReviewInputDto + Validator -> Rate Limiter -> SpamChecker
        -> ReviewService -> Mapper -> ReviewRepository -> PostgreSQL
        -> redirect + flash
```

The controller owns HTTP concerns; the form owns input mapping; the service coordinates the use case; the spam checker owns deterministic rules; and the repository owns persistence.

### Public reads

```text
Browser -> Controller -> ReviewRepository -> PostgreSQL
        -> Review data -> Twig -> escaped HTML
```

Templates receive only public data and never render `authorEmail`.

### Aggregate reads

```text
Browser -> Companies action -> ReviewRepository
        -> COUNT + AVG + GROUP BY in PostgreSQL
        -> CompanyStats models -> Twig
```

Aggregation stays in the database. A deterministic secondary order (normalized company name ascending) makes equal averages stable and testable.

## 5. High-level design

```text
+---------+     +-------+     +------------------------------------+
| Browser | --> | Nginx | --> | Symfony / PHP-FPM                  |
+---------+     +-------+     | Controllers -> Form/DTO -> Service |
                              |                 |-> SpamChecker     |
                              |                 |-> Repository      |
                              | Controllers -------------> Twig     |
                              +------------------+-----------------+
                                                 |
                                          +------v-------+
                                          | PostgreSQL 16|
                                          +--------------+
```

- **Controllers:** HTTP translation only.
- **Form/DTO:** input contract and validation boundary.
- **ReviewService:** submission orchestration, not query SQL.
- **SpamChecker:** independently testable classification.
- **Rate Limiter:** limits frequency before persistence.
- **ReviewRepository:** persistence, search, and aggregate queries.
- **Twig:** escaped server-rendered HTML.
- **PostgreSQL:** durable storage, filtering, and aggregation.

Deliberately omitted: a repository interface with only one implementation, routine output DTOs without an exposure problem, event/command buses, queues, and separately deployed services.

## 6. Deep dives

### Aggregation and query performance

PostgreSQL performs `COUNT`, `AVG`, grouping, and ordering, avoiding raw-row transfer to PHP. An index supporting company lookup should be added and checked against the real query plan, but a B-tree does not make unbounded full aggregation free.

At measured higher load: cache the leaderboard briefly, use a summary table/materialized view, refresh aggregates transactionally or asynchronously according to freshness needs, add read replicas for public reads, and partition only when table size/access patterns justify it.

### Company identity

`Acme`, `ACME`, and `Acme Ltd.` may fragment statistics. Initial comparisons should be consistent while preserving display text. A mature model adds a stable `Company` ID, canonical name, aliases, controlled matching, and a migration path for free text.

### Spam and abuse

Version one layers CSRF, honeypot, configurable keyword checks, rate limiting, and strict lengths/formats. These reduce abuse but do not prove legitimacy. Evolution may add risk scoring, challenge-on-suspicion CAPTCHA, reputation, asynchronous classification, and a moderation queue with audit history. Rejection metrics and false positives must be observed before tightening rules.

### Privacy

The task requires email collection, not publication. Avoid rendering or logging it, define purpose and retention, support deletion/anonymization, and restrict database access. Encrypt it if recoverability is necessary; use keyed hashing if only comparison is needed. The business purpose determines the right transformation.

### Search

Start with a bound, case-insensitive PostgreSQL query. Trigram indexes can later support fuzzy matching. Elasticsearch or Meilisearch becomes justified only with broader relevance, typo tolerance, facets, or indexing-scale requirements.

### Reliability and duplicates

One accepted review is one atomic database write. POST/Redirect/GET prevents refresh resubmission, not retries or malicious duplicates. Future API clients or background work may justify idempotency tokens, bounded duplicate fingerprints, transactional outbox records, retries, and dead-letter handling.

### Caching and consistency

Direct reads provide simple strong consistency. Reviews and especially aggregate pages can later use HTTP/application caches with short TTLs, explicit invalidation, or asynchronously refreshed materialized data. Define acceptable staleness before choosing.

### Observability

Measure route latency/errors, database and aggregate-query latency, accepted/invalid/spam/rate-limited submissions, and container health. Use structured errors with correlation IDs while excluding personal data. These signals determine whether scaling work is necessary.

## 7. Testing strategy

- Unit tests cover deterministic spam rules and focused mapping/service behavior.
- Repository integration tests seed known reviews and verify database averages, counts, descending order, and ties.
- Functional tests exercise routes, validation, successful submission and flash, rejection, details, search, and statistics.
- Tests use an isolated database and deterministic fixtures and run via `php bin/phpunit` inside Docker.
- Static analysis and style checks complement, rather than replace, behavioral tests.

## 8. Key decisions

| Decision | Rationale |
| --- | --- |
| Docker + PostgreSQL | No host PHP dependency and realistic persistence |
| Modular monolith | Smallest clean architecture for the requirements |
| Input DTO | Isolates form and honeypot state from persistence |
| Concrete repository | Meets the spec without a speculative interface |
| Database aggregation | Uses the database efficiently and reduces transfer |
| Private author email | Collection is required; disclosure is not |
| Pre-persistence spam rejection | No moderation workflow/status is required |
| Search + layered abuse controls | Relevant, demonstrable differentiators |
| Separate scaling documentation | Shows foresight without burdening version one |
