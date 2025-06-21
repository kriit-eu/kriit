# GitHub Copilot Instructions for This Project

## Codebase Editing Guidelines

- Eliminate duplication by removing repeated patterns wherever possible.
- Merge similar functions into reusable components.
- Remove redundant abstractions by inlining single-use variables or functions if it improves clarity.
- Use modern ES6+ syntax for cleaner, more concise code.
- Streamline logic by combining related operations where it improves structure.
- Reduce code safely whenever possible.

### Legibility and Maintainability

- Avoid cryptic tricks, short names, and nested ternaries (no deeper than one level).
- Code must be clear and maintainable for any developer.
- Favor readability over brevity, but keep code concise when possible.
- Use descriptive names, even if longer.
- Group related code blocks together.
- Simplify complex structures and avoid deep nesting.
- Break long lines at logical boundaries.
- Keep formatting consistent and uniform.
- Do not stack multiple statements with semicolons.
- Preserve whitespace between unrelated sections.
- Use parentheses to clarify precedence in complex expressions.
- Handle errors safely; do not sacrifice safety for brevity.
- No comments: Write self-documenting code. Do not add comments, especially those describing recent changes.
- Remove all informative logging to console, but keep warnings and error logging.

## Database and Data Handling

- Use migrations or versioned scripts for all schema changes.
- Keep database logic simple and avoid unnecessary complexity.
- Use clear, descriptive names for tables, columns, and queries.
- Avoid raw queries when possible; use safe abstractions or prepared statements.
- Ensure data integrity and handle errors robustly.

_These rules are mandatory for all code contributions. Review your code for compliance before submitting pull requests._
