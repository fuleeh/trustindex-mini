# Trustindex Mini-Application — Execution Workflow

This is the implementation plan for the Trustindex Medior PHP Developer test. The original Hungarian requirements are the source of truth; [ARCHITECTURE.md](ARCHITECTURE.md) records the design reasoning.

## Working agreements

- Start from a new Symfony 7.4 project.
- Run PHP, Composer, Console, tests, and quality tools in Docker; host PHP is not assumed.
- Use PHP 8.2+, Nginx, PHP-FPM, and PostgreSQL 16 with Docker Compose.
- Use a conventional Symfony modular monolith. Add abstractions only for concrete responsibilities.
- Bind forms to an input DTO, not directly to the Doctrine entity.
- Keep query logic in the concrete `ReviewRepository`; no speculative repository interface.
- Coordinate submission and abuse checks in a small application service.
- Use English for code/docs, preserving required Hungarian UI text and `Munkanapló`.
- Every PHP file uses strict types. PHPStan level 8, PHPUnit, and PHP-CS-Fixer are gates.
- Log actual time in README as each phase ends; never invent it afterward.
- Never weaken a test or quality gate simply to make it pass.

## Scope

Required: review submission and validation, public list, detail page, company counts and averages ordered by average descending, Doctrine migration, unit and functional tests, runnable README, and work log.

Differentiators: company search, honeypot and keyword spam checks, submission rate limiting, private handling of author email, typed input/read models, and architecture/scaling documentation.

Non-goals: authentication, review editing/deletion, moderation UI or stored moderation status, external review imports, a public JSON API, and distributed infrastructure in version one.

## Execution and commit policy

Work in the logical phases below. The user owns all Git commits; the coding agent must never create a commit. After each completed logical step, the agent must:

1. Run phase-appropriate tests and every available quality gate.
2. Report results, deviations, and meaningful defaults.
3. Suggest a commit message for the user.
4. Continue only after the user has had an opportunity to review the step.

Checklist items are implementation tasks, not individual commit gates. The intended history is coherent phase commits, not a commit per checkbox. If a check stays broken after two or three focused fixes, stop and show the error instead of looping or weakening it.

## Phase 0 — Design baseline

- [x] Extract functional and non-functional requirements.
- [x] Define core models, HTTP interface, data flows, and high-level design.
- [x] Record scaling, security, privacy, reliability, and testing deep dives.
- [x] Review `ARCHITECTURE.md` against the original requirements.

Suggested commit: `docs: define requirements and application architecture`

## Phase 1 — Scaffolding and tooling

- [x] Initialize Symfony 7.4 with Doctrine ORM/Migrations, Forms, Validator, Twig, Rate Limiter, PHPUnit, PHPStan, and PHP-CS-Fixer.
- [x] Add Docker services: PHP 8.2-FPM with required extensions, Nginx, and PostgreSQL 16 with health check and volume.
- [x] Configure development and isolated test databases.
- [x] Add Make targets for lifecycle, Composer, database, tests, analysis, style, and combined quality checks. All run in Docker.
- [x] Configure PHPStan level 8, Symfony PHP-CS-Fixer rules, PHPUnit, environment examples, and ignores.
- [x] Verify a clean image build and Symfony boot.

Suggested commit: `chore: scaffold Symfony application and Docker tooling`

## Phase 2 — Persistence and query model

- [ ] Create attribute-mapped `Review`: generated ID, company name, rating, text, author email, and automatic immutable timestamps.
- [ ] Create `ReviewRepository` with case-insensitive company search and database-side `COUNT`/`AVG` grouped by company.
- [ ] Order statistics by average descending and add deterministic secondary ordering.
- [ ] Return aggregates as a typed `CompanyStats` read model where practical.
- [ ] Add indexes justified by implemented queries.
- [ ] Generate and inspect a Doctrine migration, run it on PostgreSQL, and validate the schema.

Suggested commit: `feat: add review persistence and company statistics`

## Phase 3 — Submission and abuse controls

- [ ] Add validated `ReviewInputDto`, including an unmapped honeypot value.
- [ ] Add a focused DTO-to-entity mapper if it keeps orchestration concise.
- [ ] Add an independently testable spam checker with honeypot and configurable case-insensitive keywords.
- [ ] Configure Symfony Rate Limiter for submissions.
- [ ] Add `ReviewService` to coordinate checks, mapping, and persistence.
- [ ] Reject spam before persistence; do not add an undocumented status column.
- [ ] Ensure author email is never exposed publicly.
- [ ] Unit-test validation, spam rules, mapping, and service behavior where valuable.

Suggested commit: `feat: add validated review submission and abuse protection`

## Phase 4 — HTTP interface and UI

- [ ] Add CSRF-protected `ReviewType` bound to the input DTO, with accessible star-style rating radios.
- [ ] Implement:

  | Method | Route | Purpose |
  | --- | --- | --- |
  | `GET` | `/` | Reviews and submission form |
  | `POST` | `/reviews` | Submit a review |
  | `GET` | `/reviews/{id}` | Review detail |
  | `GET` | `/companies` | Company aggregates |
  | `GET` | `/search?q=...` | Company search |

- [ ] Use POST/Redirect/GET and the exact success flash `Köszönjük a véleményed!`.
- [ ] Provide useful validation, generic spam, and rate-limit feedback; surface HTTP 429 semantics where compatible with the form flow.
- [ ] Add a shared Twig layout and responsive list, detail, companies, and search pages.
- [ ] Keep Twig auto-escaping enabled and never render author email.
- [ ] Use Bootstrap or small local CSS; avoid unnecessary frontend build complexity.

Suggested commit: `feat: add review pages, search, and star rating UI`

## Phase 5 — Verification

- [ ] Unit-test pure rules and focused service behavior.
- [ ] Integration-test repository averages, counts, descending order, and tie ordering using known database records.
- [ ] Functional-test index, valid/invalid submission, flash message, spam rejection, statistics, detail, and search.
- [ ] Confirm database isolation and run `php bin/phpunit` in Docker without errors.
- [ ] Run PHPStan level 8, PHP-CS-Fixer check mode, and the combined `make code-quality` target.

Suggested commit: `test: cover review flows and aggregate ordering`

## Phase 6 — Documentation and final QA

- [ ] Write README: description, features, stack, Docker quick start, required Composer/Symfony/database/test commands, architecture summary, security decisions, and accurate `Munkanapló`.
- [ ] Write `SCALING.md` from the deep dives, clearly separating current behavior from future options.
- [ ] Rebuild from a clean Docker state, migrate, exercise browser flows, and run `make code-quality`.
- [ ] Confirm only intended files are present and `main` is runnable.

Suggested commit: `docs: complete setup, work log, and scaling notes`

## Definition of done

- [ ] All mandatory requirements and agreed bonuses work.
- [ ] The Doctrine migration runs on PostgreSQL.
- [ ] Unit, integration, and functional tests pass in Docker, explicitly covering average and ordering logic.
- [ ] PHPStan level 8 and style checks pass.
- [ ] Author email is stored but never displayed publicly.
- [ ] README contains accurate commands and actual work time.
- [ ] Architecture/scaling docs distinguish implemented design from future evolution.
- [ ] Git history contains coherent, reviewed commits.
