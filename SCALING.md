# Scaling and Production Evolution

The current application is a Dockerized Symfony modular monolith backed by one PostgreSQL database. That is the appropriate deployment shape for the assignment and for an early product with modest traffic. The options below are future responses to measured constraints, not components already deployed.

## Current limitations

- Company identity is a free-text name stored on each review.
- Review lists and company statistics query the primary database directly.
- Company statistics are calculated on demand.
- Search is a case-insensitive substring query.
- Spam checks are synchronous and deterministic.
- Rate limiting uses Symfony's configured local cache and client IP.
- Review submission has no authenticated identity or idempotency key.
- The application runs as a single PHP/Nginx deployment without centralized observability.

## Database growth

The current aggregate query correctly lets PostgreSQL calculate `COUNT` and `AVG`. The company-name and creation-date indexes support present query patterns, but indexes do not make an unbounded full aggregation free.

As measured volume grows:

1. Inspect real query plans with `EXPLAIN (ANALYZE, BUFFERS)` before changing schema.
2. Add pagination or cursor-based navigation to review lists.
3. Introduce a canonical `Company` entity and reference it by stable ID.
4. Consolidate aliases such as `Acme`, `ACME`, and `Acme Ltd.` during migration.
5. Maintain company aggregates in a summary table or materialized view if on-demand grouping becomes expensive.
6. Add read replicas for high-volume public reads while keeping writes on the primary.
7. Consider time-based partitioning only when table size, retention, and query plans demonstrate a benefit.

## Caching and consistency

Review detail pages, list fragments, and especially company rankings are cache candidates. The present direct-query model provides simple strong consistency.

A scaled design should first define acceptable staleness, then choose among:

- HTTP caching for anonymous read pages;
- short-lived application caching for company statistics;
- explicit aggregate-cache invalidation after accepted reviews;
- asynchronously refreshed materialized results for bounded eventual consistency.

Cache keys must include relevant query parameters, pagination, and locale. Personal data must never be placed in shared public caches.

## Search

PostgreSQL remains sufficient for basic case-insensitive company search. Before operating a separate search cluster, add normalized company identifiers and consider PostgreSQL trigram indexes for typo-tolerant matching.

Elasticsearch or Meilisearch becomes justified when requirements include multi-field relevance ranking, facets, synonyms, typo tolerance at large scale, or cross-source indexing. Index updates should then be driven through a transactional outbox to avoid losing changes between PostgreSQL and the search service.

## Asynchronous work

Persisting a review should remain a small, atomic transaction. Expensive or non-critical work can move to Symfony Messenger workers:

- email notifications;
- advanced spam classification;
- search indexing;
- aggregate refreshes;
- analytics events.

A production queue needs retry limits, exponential backoff, idempotent handlers, dead-letter handling, and monitoring. An outbox record written in the same database transaction as the review prevents dual-write inconsistencies.

## Spam, rate limiting, and moderation

The initial honeypot, keyword filter, and IP limiter are inexpensive first-line controls. They can produce false positives and do not prove human legitimacy.

Evolution may include:

- shared Redis-backed rate-limit state across PHP instances;
- trusted-proxy configuration so the real client IP is used safely;
- risk scoring from IP, device, velocity, and content signals;
- CAPTCHA only after suspicious behavior rather than for every visitor;
- asynchronous external or ML classification;
- a moderation state machine and audited reviewer queue;
- account or email verification if product requirements permit it.

Track accepted, validation-rejected, spam-rejected, and rate-limited counts without recording raw review text or email in metrics.

## Reliability and duplicate prevention

POST/Redirect/GET prevents refresh resubmission but not network retries or intentional duplicates. API clients or unreliable networks may justify:

- short-lived idempotency keys scoped to a client;
- bounded duplicate fingerprints;
- database uniqueness rules only where business semantics are unambiguous;
- graceful retry behavior for transient database failures;
- health/readiness checks that distinguish process availability from database readiness.

Backups must be tested through restoration exercises. Migration rollout should remain backward-compatible when multiple application versions can run simultaneously.

## Privacy and data lifecycle

Author email is required but not public. A production system should define its purpose, access policy, and retention period before collecting it indefinitely.

Further controls include:

- preventing personal data from entering logs, traces, cache keys, and analytics;
- encryption at rest and restricted database roles;
- application-level encryption if operational access must be minimized;
- keyed hashing instead when only equality comparison is required;
- deletion or anonymization workflows for privacy requests;
- documented backup-retention and deletion behavior.

## Horizontal scaling

Nginx and PHP-FPM instances are stateless except for session and limiter state. To run multiple instances:

- move sessions and rate-limit storage to Redis or another shared store;
- keep uploaded/runtime files outside individual containers;
- terminate TLS at a load balancer or ingress;
- configure trusted proxies explicitly;
- run database migrations as a controlled deployment job, not concurrently in every web container.

Scale PHP workers using measured latency, CPU, memory, and queue depth rather than request count alone.

## Observability

A production deployment should provide:

- structured logs with correlation IDs and no personal data;
- request rate, error rate, and latency by route;
- database connection, query latency, and slow-query metrics;
- spam and rate-limit outcome counters;
- queue depth, retry, and dead-letter metrics when workers exist;
- container CPU, memory, restart, health, and saturation signals;
- alerts tied to user-visible service objectives.

These measurements determine which scaling investment is needed and prevent speculative complexity.
