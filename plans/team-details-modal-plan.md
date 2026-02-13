## Team Details Modal Plan

The goal is to display detailed information about a team, combining user-inputted scouting data and external data from The Blue Alliance (TBA) API, within a modal on the existing `tba-viewer.html` page.

### 1. HTML Modifications (`www/scoutingapp/html/tba-viewer.html`)

*   Add a new `div` element for the team details modal within `tba-viewer.html`, similar to the existing modal structure in `script.js`. This modal will initially be hidden.
*   Modify the `renderTeamsTable` function to make the `team_number` clickable. Each clickable team number will trigger the display of the team details modal.
*   Add a `div` element to `tba-viewer.html` to act as a container for displaying the additional TBA data specific to a team (e.g., event data, awards, etc.).

### 2. CSS Modifications (`www/scoutingapp/css/styles.css`)

*   Add CSS rules for the new team details modal to ensure it is centered, responsive, and visually consistent with the existing theme. This will include styles for the modal overlay, content, header, body, and close button.

### 3. JavaScript Modifications (`www/scoutingapp/js/script.js`)

*   **New Modal Functionality:**
    *   Create a new function, `showTeamDetailsModal(teamNumber)`, which will be responsible for:
        *   Fetching user-inputted data from Supabase for the given `teamNumber`.
        *   Fetching additional team data from The Blue Alliance API using the `teamNumber`.
        *   Combining and formatting this data.
        *   Populating the team details modal with the combined data.
        *   Displaying the modal.
*   **Integration with `renderTeamsTable`:**
    *   In the `renderTeamsTable` function, add an `onclick` event listener to each `team_number` cell to call `showTeamDetailsModal()` with the respective `team_number`.
*   **Supabase Data Retrieval:**
    *   Extend existing Supabase logic to query `scouting_entries` table for all entries related to the selected `teamNumber`.
*   **TBA API Data Retrieval:**
    *   Implement a function to fetch specific team data from the TBA API (e.g., `/team/{team_key}/events`). The `team_key` format is typically `frcXXXX` where `XXXX` is the team number.

### 4. Data Display within Modal:

*   The modal will present two main sections:
    *   **User Input Data:** A summary or list of the scouting entries from Supabase for the selected team.
    *   **Blue Alliance Data:** Key information about the team from TBA, such as team name, location, rookie year, and a list of events they participated in, possibly with links to their TBA profile.

### Workflow Diagram

```mermaid
graph TD
    A[User Clicks Team #] --> B{Call showTeamDetailsModal(teamNumber)};
    B --> C{Fetch Scouting Data from Supabase};
    B --> D{Fetch TBA Data for Team (e.g., team events)};
    C --> E[Format Supabase Data];
    D --> F[Format TBA Data];
    E & F --> G[Combine Data];
    G --> H[Populate Team Details Modal];
    H --> I[Display Modal];
    I --> J[User Interacts with Modal / Closes Modal];
```
