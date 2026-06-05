# Modules — Jasanika Modular Foundation (M58)

This document describes the basic module system introduced in milestone M58.

- Directory: inc/core contains bootstrap, module-registry.php and module-loader.php
- Modules are logical groups (dashboard, seo-manager, slider-manager, etc.)
- At M58 modules are registered only; code remains in existing files for backward compatibility.

Registration

Use jasanika_register_module( string $id, array $meta ) to register modules. Metadata fields:
- name: Human-friendly name
- version: Theme-specific version (format: 0.<milestone>.0)
- description: Short description
- enabled: bool

Loading

jasanika_load_modules() marks enabled modules as loaded. Future milestones will map modules to filesystem files and perform lazy-loading.

Diagnostics

Diagnostics now exposes registered and loaded modules on the Diagnostics admin page.

Security

IDs are sanitized and duplicate registrations are prevented.
