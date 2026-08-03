# Orient Workflow Plugin for GLPI 11

Author: Muhammad Usman Khalid

---

# Project Goal

Develop a production-ready GLPI 11 plugin called **Orient Workflow**.

The plugin must automatically route tickets created through **GLPI Forms (Service Catalog)**.

The plugin should be clean, modular, object-oriented and easy to maintain.

No quick fixes.
No duplicate code.
No procedural spaghetti code.

---

# Technology

- GLPI 11.x
- PHP 8.2+
- MariaDB
- Forms Plugin
- PSR Coding Style
- Object Oriented Design

---

# Ticket Source

Tickets are NOT created manually.

All tickets are created from:

GLPI
→ Service Catalog
→ Forms Plugin

After ticket creation, the routing engine must execute automatically.

---

# Branches

- Head Office
- FSD

---

# Services

- IT Support
- SAP Support

---

# IT Categories

- Hardware
- Software
- Printer
- Email
- Internet
- VPN
- Access Request

---

# SAP Categories

- SAP ABAP
- SAP Basis
- SAP FI
- SAP MM
- SAP PP
- SAP SD
- SAP HCM

---

# Database Table

Table Name

glpi_plugin_orientworkflow_routes

Columns

- id
- branch
- service
- category
- group_id
- technician_id
- assignment_mode
- itilcategories_id
- priority
- sla_id
- entity_id
- is_active
- date_creation
- date_mod

---

# Routing Logic

## IT Support

Routing must match

Branch
+
Service
+
Category

Example

Head Office
IT Support
Hardware

↓

Assign

IT Support HO Group

---

Example

FSD
IT Support
Printer

↓

Assign

IT Support FSD Group

---

# SAP Support

Branch must be ignored.

Routing must only match

Service
+
Category

Example

Head Office

SAP Support

SAP ABAP

↓

Assign

SAP ABAP Group

Exactly the same route should also work for

FSD

SAP Support

SAP ABAP

because Branch is ignored for SAP.

---

# Assignment Modes

Two assignment modes are required.

---

## Fixed Technician

Assign the configured technician.

Example

SAP ABAP

↓

Muhammad Ali

Every ticket goes to Muhammad Ali.

---

## Round Robin

Assign technicians inside the selected group.

Example

Ali

Usman

Ahmed

Ali

Usman

Ahmed

Store the last assigned technician so rotation survives server restart.

---

# Admin Panel

Create Routing Rules screen.

Fields

- Branch
- Service
- Category
- Assign Group
- Assign Technician
- Assignment Mode
- ITIL Category
- Priority
- SLA
- Active

---

# Dynamic Category Dropdown

When Service changes

IT Support

Show

- Hardware
- Software
- Printer
- Email
- Internet
- VPN
- Access Request

When Service changes

SAP Support

Show

- SAP ABAP
- SAP Basis
- SAP FI
- SAP MM
- SAP PP
- SAP SD
- SAP HCM

Must use AJAX.

---

# Technician Dropdown

When Group changes

Load technicians of that group using AJAX.

Only technicians belonging to selected group must appear.

---

# Ticket Assignment

Assign Group

Use

Group_Ticket

Actor Type

CommonITILActor::ASSIGN

Assign Technician

Use

Ticket_User

Actor Type

CommonITILActor::ASSIGN

---

# Ticket Update

Automatically update

- SLA
- Priority
- ITIL Category

using Ticket::update()

---

# Ticket Visibility

Technicians must NOT see every ticket.

Technicians should only see

- tickets assigned to themselves

OR

- tickets assigned to their group

GLPI permissions must remain compatible.

No custom visibility hacks.

---

# Ticket Information

Technician should immediately know

- Branch
- Service
- Category

without opening Form Answers.

Plugin should copy these values into ticket fields or prepend them to title.

Example

[FSD]

SAP Support

SAP ABAP

Printer not working

---

# Logging

Create log

files/_log/orientworkflow.log

Log

- Request received
- Parsed answers
- Branch
- Service
- Category
- Route found
- Group selected
- Technician selected
- Assignment Mode
- SLA
- Priority
- Errors
- Exceptions

---

# Project Structure

orientworkflow/

setup.php

hook.php

inc/

RoutingEngine.php

RouteRepository.php

AssignmentService.php

RoundRobinService.php

Logger.php

FormParser.php

Config.php

front/

routing.form.php

config.form.php

ajax/

get_categories.php

get_technicians.php

js/

routing.js

css/

routing.css

README.md

---

# Architecture

Service Catalog

↓

Forms Plugin

↓

Hook

↓

Form Parser

↓

Routing Engine

↓

Repository

↓

Route Found

↓

Assignment Service

↓

Assign Group

↓

Assign Technician

↓

Assign SLA

↓

Assign Priority

↓

Update Ticket

↓

Done

---

# Coding Rules

Must use

- OOP
- Dependency Separation
- Small methods
- No duplicated code
- PHPDoc
- Exception handling
- GLPI APIs wherever possible
- Prepared DB requests
- Meaningful class names

Do NOT write everything inside one file.

---

# Future Features

Architecture must allow future implementation of

- Multi Level Approval
- Department Routing
- Email Notifications
- Escalations
- Business Hours
- Power BI Reports
- Dashboard Widgets
- Workflow History
- Audit Logs

without rewriting existing code.

---

# Important

This plugin will be used in production.

Write production-quality code.

Do not generate placeholder code.

Do not leave TODO comments.

Do not skip implementations.

Every feature described above must be fully implemented.

Code should be modular and maintainable.
