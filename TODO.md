## Project Dolive: Development Checklist

This document outlines the tasks required to develop the Dolive web application, a platform similar to GoStream.

### Phase 1: Planning and Setup

- [X] **1. Analyze Project Requirements and Features:** Thoroughly review the user's request and the provided information about GoStream to understand all required functionalities for Dolive. This includes user authentication, admin management, video uploading, livestreaming capabilities, multi-platform broadcasting, usage limitations for new accounts, and a user interface similar to GoStream but distinct.
- [X] **2. Design System Architecture:** Define the overall architecture of Dolive, including the roles of PHP, MySQL, and Node.js. Plan the database schema, API endpoints, and how different components will interact. Consider scalability and security. (Switched to SQLite for development due to sandbox issues with MySQL/MariaDB).
- [X] **3. Set Up Development Environment:** Prepare the local development environment with PHP, Node.js, SQLite, and any necessary frameworks or libraries. Set up version control (e.g., Git). (PHP 8.1, Composer, Node.js v12.22.9, SQLite 3.37.2 installed).
- [X] **4. Resolve MySQL/MariaDB Port Conflict and Service Failure or Switch to Alternative DB:** Encountered persistent issues with MySQL/MariaDB in the sandbox. Switched to SQLite for development to ensure project progress. (SQLite installed and confirmed working).

### Phase 2: Core Feature Development

- [X] **5. Implement User Authentication and Authorization:** Develop modules for user registration, login, password management, and role-based access control (user, admin) using PHP (Laravel recommended) and SQLite. (Laravel Breeze with Blade installed and migrations run successfully with SQLite).
- [X] **6. Develop Admin and User Management Features:** Create an admin panel for managing users (view, edit, delete, set limits), viewing system statistics, and other administrative tasks. Implement user profile management using PHP and SQLite. (AdminController, routes, middleware, and basic Blade views for dashboard, user listing, and user editing are implemented).
- [X] **7. Build Video Upload and Storage Module:** Develop functionality for users to upload pre-recorded videos. Implement a secure and efficient video storage solution. Store metadata in SQLite. (VideoController, routes, migration, and Blade views for video listing, upload, and editing are implemented; storage link created).
- [X] **8. Create Livestreaming and Multi-platform Broadcasting Features:** This is a core module. Research and implement how to take an uploaded video and stream it as if it were live using Node.js and FFmpeg. Develop integrations for broadcasting to multiple platforms (e.g., using RTMP for Facebook, YouTube, etc.). (Node.js streaming service with basic API, Laravel backend integration with LivestreamController, models, migrations, routes, and Blade views for creating/managing livestreams are implemented. Basic communication with Node.js service for start/stop is in place).
- [X] **9. Implement Usage Limitation for New Accounts:** Design and implement a system to apply specific usage limits for newly registered accounts (e.g., number of concurrent streams, total streaming time, number of platforms), managed via PHP and SQLite. (Migration for user limits created, AdminController updated to manage limits, LivestreamController updated to enforce limits).

### Phase 3: User Interface and Integration

- [ ] **10. Design and Implement User Interface (UI):** Create a user-friendly interface for Dolive using PHP (server-side rendering) and JavaScript. The UI should be similar in feel to GoStream but have its own distinct visual identity. Ensure responsive design for different devices. This includes the main dashboard, video management, streaming setup, account settings, and admin panel.
- [ ] **11. Validate Features and System Integrity:** Conduct thorough testing of all implemented features, including unit tests, integration tests, and user acceptance testing. Ensure the system is stable, secure, and performs as expected with PHP, Node.js, and SQLite.

### Phase 4: Deployment and Delivery

- [ ] **12. Prepare for VPS Deployment and Port 80 Configuration:** Prepare the application for deployment on a Virtual Private Server (VPS). This includes creating deployment scripts, configuring the server environment (web server like Apache/Nginx for PHP, Node.js runtime), setting up MySQL/MariaDB on the VPS (revisiting this choice for production), and ensuring the application runs on port 80.
- [ ] **13. Report Progress and Deliver Code to User:** Provide a final report on the project, deliver the source code, and any necessary documentation for deployment and maintenance.
