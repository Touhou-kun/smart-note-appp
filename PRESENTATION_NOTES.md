# Smart Note App - Presentation Notes

## Login

Feature Name: Login

Objective: Allow registered users to securely access their personal notes.

User Workflow: User enters email and password. The controller validates CSRF token and credentials. If credentials are correct, the session is regenerated and the user is redirected to Dashboard.

Database Design: Uses the `users` table with `email` and hashed `password`.

MVC Flow: `AuthController` receives the request, `User` model finds the account, and `auth/login.php` displays the form.

UI Description: Simple authentication form with email, password, and login button.

Result: Only valid users can access protected pages.

Advantages: Protects private notes and keeps each user's data separated.

## Register

Feature Name: Register

Objective: Allow new users to create an account.

User Workflow: User fills username, email, password, and confirmation. The app validates the input, hashes the password, and stores the user.

Database Design: Stores records in `users`.

MVC Flow: `AuthController::store()` validates input and calls `User::create()`.

UI Description: Clean registration form with a link back to login.

Result: New users can log in and manage their own notes.

Advantages: Supports multiple users with secure password storage.

## Notes CRUD

Feature Name: Notes CRUD

Objective: Let users create, view, update, and delete notes.

User Workflow: User creates a note with title, content, category, tags, priority, image, pin, favorite, color, or archive state. The note can later be viewed, edited, moved to recycle bin, or permanently deleted.

Database Design: Uses `notes`, `categories`, `tags`, and `note_tags`.

MVC Flow: `NoteController` handles requests, `Note` model saves or retrieves data, and notes views render forms and lists.

UI Description: Card-based notes list and structured note form.

Result: Users can manage personal notes from one interface.

Advantages: Central note management with categories, tags, and attachments.

## Sort & Filter

Feature Name: Sort & Filter

Objective: Help users find notes quickly.

User Workflow: User selects category, tag, favorite status, priority, pin status, or sort order. The notes grid updates through Fetch without a full reload.

Database Design: Uses `notes.category_id`, `notes.priority`, `notes.is_pinned`, `notes.is_favorite`, `tags`, and `note_tags`.

MVC Flow: `NoteController::filters()` builds filter values. `Note::all()` applies SQL conditions and sort order. `notes/index.php` renders the grid.

UI Description: Filter panel above the notes grid with dropdowns and search input.

Result: Notes can be sorted by newest, oldest, title A-Z, or pinned first.

Advantages: Faster navigation and better organization for large note collections.

## Favorite Notes

Feature Name: Favorite Notes

Objective: Let users mark important notes for quick access.

User Workflow: User clicks Favorite. An AJAX request sends note ID and CSRF token. The controller toggles the value. The UI updates the button and badge.

Database Design: Adds `notes.is_favorite`.

MVC Flow: `NoteController::toggleFavoriteApi()` calls `Note::toggleFavorite()` and returns JSON.

UI Description: Favorite button on each note card and Favorite badge when active.

Result: Users can filter the list to show favorite notes only.

Advantages: Important notes become easier to locate.

## Note Colors

Feature Name: Note Colors

Objective: Let users visually group notes by color.

User Workflow: User selects Yellow, Green, Blue, Pink, or Purple in the note form. The selected value is saved and restored on the note card.

Database Design: Adds `notes.color`.

MVC Flow: `NoteController::validColor()` validates the value, `Note` model saves it, and the view applies a CSS class.

UI Description: Color dropdown in the note form and colored note cards in the list.

Result: Notes have persistent visual colors.

Advantages: Improves scanning and personal organization.

## Archive Notes

Feature Name: Archive Notes

Objective: Hide old notes from the main Notes page without deleting them.

User Workflow: User clicks Archive. The note is removed from Notes and appears in Archive. User can restore it from Archive.

Database Design: Adds `notes.is_archived`.

MVC Flow: `NoteController::archive()` and `restoreArchive()` update the note through `Note::setArchived()`. `archived()` renders archived notes.

UI Description: Archive sidebar link, Archive button, and Restore button.

Result: Main Notes stays clean while archived notes remain available.

Advantages: Reduces clutter without data loss.

## Upload Images

Feature Name: Upload Images

Objective: Allow users to attach images to notes.

User Workflow: User selects an image. JavaScript previews it. On save, PHP validates size and MIME type, stores the file, and saves the filename.

Database Design: Uses `notes.image_path`.

MVC Flow: `NoteController::handleUpload()` validates and stores the file. Views display uploaded images.

UI Description: File input and preview area in the note form.

Result: Notes can include visual attachments.

Advantages: Supports richer notes and visual memory.

## Recycle Bin

Feature Name: Recycle Bin

Objective: Prevent accidental data loss.

User Workflow: User deletes a note. The note is soft deleted and appears in Recycle Bin. User can restore it or delete it forever.

Database Design: Uses `notes.is_deleted`.

MVC Flow: `NoteController::delete()`, `restore()`, and `forceDelete()` call `Note` model methods.

UI Description: Recycle Bin sidebar link with Restore and Delete Forever buttons.

Result: Deleted notes are recoverable until permanently removed.

Advantages: Safer note management.

## Categories

Feature Name: Categories

Objective: Group notes by topic.

User Workflow: User creates, edits, or deletes categories, then assigns notes to categories.

Database Design: Uses `categories` and `notes.category_id`.

MVC Flow: `CategoryController` calls `Category` model and renders `categories/index.php`.

UI Description: Category management table and note form dropdown.

Result: Notes can be filtered by category.

Advantages: Improves structure and browsing.

## Tags

Feature Name: Tags

Objective: Allow flexible note labeling.

User Workflow: User creates tags and attaches multiple tags to a note.

Database Design: Uses `tags` and `note_tags`.

MVC Flow: `TagController` manages tags. `Note::syncTags()` stores note-tag relationships.

UI Description: Tag management page and checkbox pills in the note form.

Result: Notes can have multiple tags and can be searched or filtered by tag.

Advantages: More flexible than categories because one note can have many tags.

## Auto Save

Feature Name: Auto Save

Objective: Reduce the risk of losing quick dashboard edits.

User Workflow: User edits dashboard note title or content. JavaScript waits briefly, sends data by Fetch, and updates the save indicator.

Database Design: Updates existing `notes.title` and `notes.content`.

MVC Flow: `NoteController::autoSave()` validates note ownership and calls `Note::update()`.

UI Description: Editable dashboard note cards with save indicator.

Result: Dashboard edits can be saved without opening the edit form.

Advantages: Faster editing experience.

Status Note: This feature is partial because full create/edit draft recovery and exact 2 second debounce coverage are not complete.

## Drag & Drop Notes

Feature Name: Drag & Drop Notes

Objective: Allow users to manually arrange notes in a natural card grid.

Purpose: Users often want important notes near the top or grouped visually. Drag and drop gives them personal control over note order.

User Workflow: User opens Notes, holds a non-interactive part of a note card, drags it to a new position, and drops it. The app automatically saves the new order.

MVC Flow: `app/views/notes/index.php` renders sortable note cards. `public/assets/js/app.js` initializes SortableJS and sends the order to `api/notes/reorder`. `NoteController::reorderApi()` validates the request. `Note::updatePositions()` saves the positions.

Database Design: Uses `notes.position INT DEFAULT 0`. No new table is required.

UI Description: The entire note card can move from non-interactive areas. Buttons, links, inputs, selects, textareas, labels, and forms keep their normal behavior. The dragged card scales slightly and a placeholder shows where it will land.

Advantages: The experience feels modern, smooth, and similar to Google Keep. The note order persists after refresh, logout/login, and browser restart.

Testing Guide: Open `http://localhost/Project1/notes`, set Sort to Custom order, drag a note card from a non-interactive area, drop it, refresh the page, and confirm the order remains. Also click action buttons and form controls to confirm they do not trigger dragging.
