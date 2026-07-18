# Legacy duplicate files

These files were moved out of Laravel's autoloaded/runtime directories during
the July 2026 cleanup. They are retained only as recovery references.

- Do not require or include files from this directory in the application.
- The canonical implementations remain under `app/`, `routes/`, and
  `resources/views/`.
- Once the canonical code has been verified and committed, Git history should
  be used for recovery and this archive can be removed in a separate change.

No application data is stored in this directory.
