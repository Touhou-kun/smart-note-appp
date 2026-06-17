# Smart Note App - Project Audit Report

## MVC Architecture Overview

SMART NOTE APP uses a custom PHP MVC structure. `index.php` defines routes and dispatches requests through `app/core/Router.php`. Controllers in `app/controllers/` protect routes, validate CSRF tokens, and coordinate models and views. Models in `app/models/` contain PDO database queries. Views in `app/views/` render escaped HTML with the `e()` helper. Global configuration, session handling, URL helpers, upload paths, CSRF helpers, and flash messages are defined in `config/config.php`.

## Database Overview

The database is `smart_note_app`.

Important tables:

- `users`: registered user accounts.
- `categories`: user-owned categories.
- `tags`: user-owned tags.
- `notes`: user-owned notes with category, title, content, priority, image, pin, favorite, color, archive, and recycle-bin fields.
- `note_tags`: many-to-many relationship between notes and tags.

New additive columns in `notes` for existing databases. Run these only after confirming the columns or index are not already present:

```sql
ALTER TABLE notes ADD COLUMN is_favorite TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE notes ADD COLUMN color VARCHAR(20) NOT NULL DEFAULT 'yellow';
ALTER TABLE notes ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE notes ADD COLUMN position INT NOT NULL DEFAULT 0;
ALTER TABLE notes ADD INDEX idx_notes_user_archive_favorite (user_id, is_archived, is_favorite);
ALTER TABLE notes ADD INDEX idx_notes_user_position (user_id, position);
```

## Feature Audit

| Feature | Status | Files | Notes |
| --- | --- | --- | --- |
| Login | Completed | `AuthController.php`, `User.php`, `auth/login.php` | Uses password verification, session regeneration, CSRF. |
| Register | Completed | `AuthController.php`, `User.php`, `auth/register.php` | Validates fields, email format, duplicate email, and password length. |
| Logout | Completed | `AuthController.php`, `layouts/app.php` | Clears session and destroys cookie. |
| Session Authentication | Completed | `config.php`, `Controller.php` | `requireAuth()` protects application routes. |
| Route Protection | Completed | Controllers | Dashboard, notes, categories, tags, recycle bin, and profile require login. |
| CRUD Notes | Completed | `NoteController.php`, `Note.php`, notes views | Create, list, edit, view, update, and delete exist. |
| Categories | Completed | `CategoryController.php`, `Category.php`, `categories/index.php` | User-scoped category CRUD. |
| Tags | Completed | `TagController.php`, `Tag.php`, `tags/index.php` | User-scoped tag CRUD and note-tag sync. |
| Search Notes | Completed | `Note.php`, `notes/index.php` | Searches title, content, and tag names. |
| Pin Notes | Completed | `NoteController.php`, `Note.php`, notes views | Pin/unpin exists through form and dashboard API. |
| Auto Save | Partial | `NoteController.php`, `dashboard/index.php` | API exists and dashboard uses Fetch; create/edit form autosave and full 2 second debounce coverage are incomplete. |
| Draft Recovery | Missing | None | No localStorage draft recovery workflow exists. |
| Upload Images | Completed | `NoteController.php`, `notes/form.php`, `app.js` | Validates MIME and size, stores file, saves path, displays image, and previews before upload. |
| Recycle Bin | Completed | `NoteController.php`, `Note.php`, `notes/index.php` | Soft delete list exists. |
| Restore Deleted Notes | Completed | `NoteController.php`, `Note.php` | Restores soft-deleted notes. |
| Permanent Delete | Completed | `NoteController.php`, `Note.php` | Deletes note and image permanently. |
| Empty Trash | Missing | None | Individual permanent delete exists; one-click empty trash does not. |
| Sort & Filter | Completed | `Note.php`, `NoteController.php`, `notes/index.php`, `app.js` | Added sort by newest, oldest, title A-Z, pinned first; filters by tag, category, favorite; Fetch updates list without reload. |
| Favorite Notes | Completed | `database.sql`, `Note.php`, `NoteController.php`, `notes/index.php`, `app.js` | Added `is_favorite`, favorite toggle, badge, and favorite filter. |
| Note Colors | Completed | `database.sql`, `Note.php`, `NoteController.php`, `notes/form.php`, `style.css` | Added `color` column and yellow, green, blue, pink, purple card styles. |
| Archive Notes | Completed | `database.sql`, `index.php`, `Note.php`, `NoteController.php`, `layouts/app.php`, notes views | Added archive route, archive action, restore archived action, and archived notes list. |
| Drag & Drop Notes | Completed | `database.sql`, `index.php`, `Note.php`, `NoteController.php`, `notes/index.php`, `layouts/app.php`, `app.js`, `style.css` | Uses SortableJS, drags whole note cards from non-interactive areas, saves order with Fetch, and persists positions in `notes.position`. |

## Newly Implemented Features

### Sort & Filter

Purpose: Helps users quickly find notes by order and note state without reloading the full page.

Implementation summary:

- Controller: `NoteController::filters()` collects search, category, tag, priority, pin, favorite, sort, deleted, and archived filters.
- Model: `Note::all()` applies safe WHERE clauses and sort options.
- View: `app/views/notes/index.php` includes Favorite and Sort controls.
- JavaScript: `public/assets/js/app.js` intercepts the filter form, fetches the filtered page, parses the notes grid, and replaces it.
- Database: Reuses existing `categories`, `tags`, `note_tags`, and `notes` columns, plus new `is_favorite`.

Workflow:

User selects filter or sort  
Fetch request loads the same route with query parameters  
Controller builds filters  
Model returns matching notes  
View renders updated note grid  
JavaScript replaces the grid without full page reload

Testing guide:

- Open Notes.
- Select a category, tag, favorite state, or sort option.
- Confirm the URL updates and the grid changes without a full page reload.
- Click Reset and confirm all active notes return.

### Favorite Notes

Purpose: Lets users mark important personal notes for quick filtering.

Implementation summary:

- Controller: `NoteController::toggleFavoriteApi()`.
- Model: `Note::toggleFavorite()`.
- View: Favorite button and badge in `notes/index.php`.
- JavaScript: AJAX toggle in `app.js`.
- Database: `notes.is_favorite`.

Workflow:

User clicks Favorite  
AJAX POST request is sent with CSRF token  
Controller verifies the note belongs to the user  
Model toggles `is_favorite`  
JSON response returns the new state  
UI updates button text and badge without reload

Testing guide:

- Open Notes.
- Click Favorite on a note.
- Confirm the badge appears.
- Filter Favorite = Favorites and confirm the note appears.
- Click Unfavorite and confirm it is removed from the favorite filter.

### Note Colors

Purpose: Gives users visual organization similar to sticky notes while preserving the project style.

Implementation summary:

- Controller: `NoteController::validColor()` validates allowed colors.
- Model: `Note::create()` and `Note::update()` save color.
- View: `notes/form.php` adds a Color select.
- CSS: `style.css` adds card color classes.
- Database: `notes.color`.

Workflow:

User selects color  
Form posts note data  
Controller validates allowed color  
Model saves color  
Notes list renders card with color class

Testing guide:

- Create or edit a note.
- Select Yellow, Green, Blue, Pink, or Purple.
- Save the note.
- Confirm the note card uses the selected color in Notes and after refresh.

### Archive Notes

Purpose: Allows users to hide notes from the main list without deleting them.

Implementation summary:

- Routes: `archived-notes`, `notes/archive`, `notes/restore-archive`.
- Controller: `archived()`, `archive()`, and `restoreArchive()`.
- Model: `Note::setArchived()`.
- View: Archive navigation and archive/restore buttons.
- Database: `notes.is_archived`.

Workflow:

User clicks Archive  
Controller sets `is_archived = 1`  
Note disappears from normal Notes  
User opens Archive  
Archived notes list is displayed  
User clicks Restore  
Controller sets `is_archived = 0`  
Note returns to normal Notes

Testing guide:

- Open Notes.
- Archive a note.
- Confirm it leaves the Notes page.
- Open Archive from the sidebar.
- Restore the note.
- Confirm it returns to Notes.

### Drag & Drop Notes

Purpose: Lets users arrange notes manually with a Google Keep style card reorder experience.

Implementation summary:

- Library: SortableJS is loaded in `app/views/layouts/app.php`.
- Controller: `NoteController::reorderApi()` receives a JSON order payload.
- Model: `Note::updatePositions()` batch updates note positions for the current user.
- View: `app/views/notes/index.php` marks the notes grid with `data-sortable-notes` and stores the reorder API URL.
- JavaScript: `public/assets/js/app.js` initializes SortableJS with animation, a 180ms delay, and filters for buttons, links, inputs, textareas, selects, labels, forms, and contenteditable elements.
- CSS: `public/assets/css/style.css` adds chosen, ghost, and drag visual states.
- Database: `notes.position`.

Workflow:

User long-presses or holds on a non-interactive note area  
SortableJS moves the entire card with smooth animation  
Other note cards shift naturally  
After drop, JavaScript builds an ordered payload  
Fetch sends the payload to `api/notes/reorder`  
Controller verifies CSRF and authentication  
Model updates `notes.position`  
Manual order remains after refresh and future login

Testing guide:

- Open `http://localhost/Project1/notes`.
- Make sure Sort is set to Custom order.
- Click and hold on a note body, image, empty card area, or metadata area.
- Drag the card left, right, up, or down.
- Drop the card and refresh the page.
- Confirm the order remains.
- Click buttons, links, filters, selects, and forms; confirm those controls do not start dragging.

## Important Files

- `index.php`: route definitions.
- `config/config.php`: configuration, sessions, CSRF, helpers.
- `app/core/Router.php`: route matching.
- `app/core/Controller.php`: view rendering and authentication guard.
- `app/models/Note.php`: note query, save, filter, toggle, archive, delete logic.
- `app/controllers/NoteController.php`: note workflows and APIs.
- `app/views/notes/index.php`: notes, recycle bin, and archive list UI.
- `app/views/notes/form.php`: note create/edit form.
- `public/assets/js/app.js`: theme, sidebar, confirmations, image preview, Fetch filtering, favorite toggles.
- `public/assets/css/style.css`: layout, cards, forms, responsive behavior, note colors.
