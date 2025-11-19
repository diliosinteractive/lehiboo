# Verification Walkthrough: Refactored Event Form (Airbnb Style)

This guide outlines the steps to verify the new "One Page" event creation/editing form, which now strictly follows the "Mon Profil" page structure and the Airbnb-style design.

## 1. Access & Layout Verification
*   **Step**: Login as a Partner and navigate to **"Créer une activité"** (or edit an existing one).
*   **Expected Result**:
    *   **Global Layout**: Side-by-side layout (Sidebar + Content).
    *   **Sticky Header**:
        *   **Left**: Title "Créer une activité".
        *   **Right**:
            *   **Status**: "Hors ligne" (grey pill) or "En ligne".
            *   **Preview**: "Prévisualiser" button (White with Orange border).
            *   **Save**: "Enregistré" button (Green background).

## 2. Navigation Test
*   **Step**: Click on the **"Localisation"** link in the inner sidebar.
    *   **Verify**: The page **smooth scrolls** to the Location section card.
    *   **Verify**: The "Localisation" link becomes **active**.
*   **Step**: Scroll manually.
    *   **Verify**: The sidebar links update their active state (**ScrollSpy**).

## 3. Section Logic Verification
*   **Step**: Test the dynamic toggles (Online/Physical, Recurring/Manual, Ticket Link/Internal).
    *   **Verify**: Fields appear/disappear correctly within their respective cards without breaking the layout.
    *   **Verify**: Inputs have the new Airbnb style (uppercase labels, black focus border).

## 4. Completion Gauge Test
*   **Step**: Check the sidebar widget.
    *   **Verify**: The progress bar updates as you fill fields.
    *   **Verify**: The "Mettre en ligne" functionality is now handled via the status toggle in the Publication section (or the save button logic if adapted).

## 5. Submission
*   **Step**: Click **"Enregistré"** in the sticky header.
    *   **Verify**: The form submits and saves data correctly.
