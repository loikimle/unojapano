# Claude AI Instructions for Knowledge Base Generation

## Purpose
This document provides comprehensive instructions for Claude AI to accurately analyze WordPress plugin code and generate factual, non-hallucinated knowledge base data.

## Core Principles

### 1. Factual Accuracy
- **ONLY extract information that is explicitly present in the code**
- **DO NOT infer or assume functionality that is not visible in the code**
- **DO NOT make up function names, class names, or features**
- If something is unclear, mark it as `null` or omit it rather than guessing

### 2. Code Analysis Guidelines

#### Menu Detection
- Look for `add_menu_page()` and `add_submenu_page()` function calls
- Identify the exact parameters: page title, menu title, capability, menu slug, callback function
- Extract parent menu relationships from `add_submenu_page()` calls
- For settings pages, look for `add_action('admin_menu')` hooks
- Identify settings tabs and sections from filter hooks like `user_registration_get_section_parts_*`

#### Module Detection
- Modules are typically in `/modules/` directory
- Look for main class files that extend or initialize modules
- Identify module entry points (files that include or require other module files)
- Extract module names from class names, file names, or directory names
- Look for module initialization hooks or filters

#### Addon Detection
- Addons are typically in `/includes/pro/addons/` or similar directories
- Look for addon initialization classes
- Identify addon-specific hooks and filters
- Extract addon features from class methods and functionality

#### Settings Detection
- **Form Settings**: Look for settings that apply to individual registration forms
  - Usually stored in form post meta
  - Settings classes in `/includes/admin/settings/` or form-specific settings files
  - Look for `get_form_setting_by_key()` or similar functions
  - Identify settings saved per form (form_id specific)

- **Global Settings**: Look for settings that apply site-wide
  - Usually stored in WordPress options table
  - Settings classes in `/includes/admin/settings/` with "general" or "global" in name
  - Look for `get_option()` calls with plugin-specific option names
  - Settings that affect all forms or the entire plugin

#### Workflow Extraction
- Trace user actions from menu clicks to function calls
- Follow AJAX handlers to understand user interactions
- Map form submissions to processing functions
- Identify validation steps, data saving, and response generation
- Extract step-by-step processes from code flow

### 3. UI Flow Construction

For each menu item, construct the UI flow as:
```
wp-admin → [Parent Menu] → [Submenu] → [Tab] → [Section]
```

Rules:
- Start with "wp-admin" as the base
- Add parent menu name (e.g., "User Registration & Membership")
- Add submenu name if it's a submenu page
- Add tab name if it's a settings tab
- Add section name if it's a settings section
- Use exact menu titles from the code, not inferred names

### 4. Function and Class Summaries

- Keep summaries to **one line** (max 100 characters)
- Describe **what** the function/class does, not **how**
- Use present tense, active voice
- Be specific: "Validates user email format" not "Does email stuff"
- If unclear, use generic description like "Handles [feature] functionality"

### 5. Module and Addon Details

For each module/addon, extract:
- **Name**: Exact module/addon name from code
- **Purpose**: What the module/addon does (from class comments or initialization)
- **Key Classes**: Main classes that handle the module
- **Hooks Used**: Action and filter hooks specific to the module
- **Settings**: Module-specific settings (form or global)
- **UI Flow**: How to access module features
- **Dependencies**: Other modules/plugins this depends on (if visible in code)

### 6. Settings Extraction

#### Form Settings Structure
```json
{
  "key": "user_registration_form_setting_example",
  "label": "Setting Label",
  "type": "text|select|checkbox|radio|textarea|number|etc",
  "default": "default_value",
  "description": "What this setting does",
  "applies_to": "form",
  "scope": "per_form",
  "options": [{"value": "option_value", "label": "Option Label"}],
  "ui_flow": "wp-admin → ... (where to find this setting)"
}
```

#### Global Settings Structure
```json
{
  "key": "user_registration_general_setting_example",
  "label": "Setting Label",
  "type": "text|select|checkbox|radio|textarea|number|etc",
  "default": "default_value",
  "description": "What this setting does",
  "applies_to": "global",
  "scope": "site_wide",
  "options": [{"value": "option_value", "label": "Option Label"}],
  "ui_flow": "wp-admin → ... (where to find this setting)"
}
```

#### Options for Select/Radio/Multiselect Fields
- For **form_settings** and **global_settings** that are `select`, `radio`, or multiselect types, extract the **options** array from the code.
- Look for: `'options' => array(...)`, `'choices' => array(...)`, `apply_filters('..._options', ...)`, or similar patterns where value/label pairs are defined.
- Each option: `{"value": "stored_value", "label": "Display label"}`. Use empty array `[]` if the field has no choices (e.g. text, checkbox) or if options are not visible in the code.
- Only include options that are **explicitly present** in the code; do not invent choices.

### 7. Workflow Documentation

For workflows, extract:
- **Trigger**: What initiates the workflow (user action, hook, etc.)
- **Steps**: Sequential steps in the process
- **Validation Points**: Where validation occurs
- **Data Storage**: Where data is saved
- **Response**: What happens after completion

Example:
```json
{
  "workflow_name": "User Registration",
  "trigger": "Form submission",
  "steps": [
    "Validate form data",
    "Check user email uniqueness",
    "Create WordPress user",
    "Save form meta data",
    "Send registration email",
    "Redirect to success page"
  ],
  "validation_points": ["Email format", "Required fields", "Password strength"],
  "data_storage": ["wp_users table", "user_meta table", "form post meta"],
  "response": "Redirect to success page or show error"
}
```

### 8. JSON Structure Requirements

- **Always return valid JSON** - no trailing commas, proper escaping
- **Use null for missing values** - not empty strings or undefined
- **Use arrays for lists** - even if empty `[]`
- **Use objects for structured data** - with consistent keys
- **No markdown code blocks** - return raw JSON only

### 9. Common Pitfalls to Avoid

❌ **DON'T:**
- Make up feature names not in code
- Assume functionality from class names alone
- Create workflows not visible in code
- Infer settings that aren't explicitly defined
- Use generic descriptions when specifics are available
- Mix form and global settings

✅ **DO:**
- Extract exact names from code
- Verify functionality by reading method implementations
- Trace actual code flow for workflows
- Distinguish between form and global settings clearly
- Use specific, accurate descriptions
- Mark unclear items as null

### 10. Code Context Analysis

When analyzing code chunks:
- **Read method implementations**, not just signatures
- **Follow include/require statements** to understand relationships
- **Check hook priorities** to understand execution order
- **Identify conditional logic** that affects functionality
- **Note database queries** to understand data structure
- **Track variable assignments** to understand data flow

### 11. Version and Compatibility

- Extract version numbers from constants or file headers
- Note WordPress version requirements from code checks
- Identify PHP version requirements from type hints or checks
- Extract plugin dependencies from activation checks

### 12. Error Handling

If code analysis is unclear:
- Mark the field as `null`
- Add a note in comments if possible
- Don't guess or infer
- Focus on what is explicitly visible

## Example Analysis Pattern

1. **Identify the file type**: Module, Addon, Settings, Core, etc.
2. **Extract classes and functions**: With accurate summaries
3. **Find menu registrations**: Exact menu structure
4. **Identify hooks and filters**: All WordPress hooks used
5. **Map settings**: Distinguish form vs global
6. **Trace workflows**: Step-by-step process flow
7. **Build UI flows**: Complete navigation paths
8. **Document modules/addons**: Purpose and usage

## Final Reminder

**Accuracy over completeness.** It's better to have fewer, accurate entries than many guessed entries. When in doubt, mark as null or omit rather than hallucinate.
