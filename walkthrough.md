# Verification Walkthrough: Refactored Event Form (Corrected Profile Style)

This guide outlines the steps to verify the new "One Page" event creation/editing form, which now strictly follows the "Mon Profil" page structure.

## 1. Access & Layout Verification
*   **Step**: Login as a Partner and navigate to **"Créer une activité"** (or edit an existing one).
*   **Expected Result**:
    *   **Global Layout**: The page should look exactly like the dashboard, with the main dark sidebar on the left, and the content area on the right.
    *   **Inner Layout**: Inside the content area, you should see a **two-column layout**:
        *   **Left Column (Sidebar)**: Event image, title, navigation tabs, and completion widget.
        *   **Right Column (Content)**: Sticky action bar at the top, followed by white cards for each section.
    *   **No Stacking**: The sidebar and content should be side-by-side, not stacked vertically (unless on mobile).

## 2. Navigation Test
*   **Step**: Click on the **"Localisation"** link in the inner sidebar.
    *   **Verify**: The page **smooth scrolls** to the Location section card.
    *   **Verify**: The "Localisation" link becomes **active**.
*   **Step**: Scroll manually.
    *   **Verify**: The sidebar links update their active state (**ScrollSpy**).

## 3. Section Logic Verification
*   **Step**: Test the dynamic toggles (Online/Physical, Recurring/Manual, Ticket Link/Internal).
    *   **Verify**: Fields appear/disappear correctly within their respective cards without breaking the layout.

## 4. Completion Gauge Test
*   **Step**: Check the sidebar widget.
    *   **Verify**: The progress bar updates as you fill fields.
    *   **Verify**: The "Mettre en ligne" button in the sticky header becomes enabled when progress is sufficient.

## 5. Submission
*   **Step**: Click **"Enregistrer"** in the sticky header.
    *   **Verify**: The form submits and saves data correctly.
