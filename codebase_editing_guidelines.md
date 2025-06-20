# 🛠️ Codebase Editing Guidelines

## 🔁 Priority #1: Simplify the Code

1. _Eliminate Duplication_ – Always make a great effort to spot and remove repeated patterns for maximum impact.
2. _Merge Similar Functions_ – Consolidate overlapping logic into reusable components.
3. _Remove Redundant Abstractions_ – Inline single-use variables/functions when it improves clarity.
4. _Use Modern Syntax_ – Prefer ES6+ features for cleaner, more concise code.
5. _Streamline Logic_ – Combine related operations where it improves structure.
6. _Reduce Code Wherever Possible_ – Always look for opportunities to trim safely.

## 👁️‍🗨️ Priority #2: Improve Legibility

1. _No Code Golf_ – Avoid cryptic tricks, short names, and nested ternaries.
2. _Prioritize Maintainability_ – Code should be clear to any developer.
3. _Favor Readability Over Brevity_ – Shorter is better and encouraged, but not at the cost of clarity.
4. _Name Things Well_ – Use descriptive names, even if longer.
5. _Group Logic Cleanly_ – Keep related code blocks together.
6. _Avoid Deep Nesting_ – Simplify complex structures whenever possible.
7. _Break Long Lines_ – Wrap at logical boundaries.
8. _Keep Formatting Consistent_ – Uniform style matters.
9. _No One-Liner Cramming_ – Don’t stack multiple statements with semicolons.
10. _Limit Nested Ternaries_ – No deeper than one level.
11. _Preserve Whitespace_ – Don’t remove spacing between unrelated sections.
12. _Clarify Precedence_ – Use parentheses around complex expressions.
13. _Handle Errors_ – Don’t sacrifice safety for brevity.
14. _No comments_ - Dont use comments! Instead write self documenting code. Especially unwelcome are comments that just comment the latest change because over time they collectively pollute and bloat the codebase and become outdated when the code changes.
15. Remove all informative logging to console but leave any warnings and error logging in place.
