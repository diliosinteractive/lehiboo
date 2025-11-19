# Verification Walkthrough: Refactored Event Form (Airbnb Style)

This guide outlines the steps to verify the new "One Page" event creation/editing form, which now strictly follows the "Mon Profil" page structure and the Airbnb-style design.

## 1. Access & Layout Verification
*   **Step**: Login as a Partner and navigate to **"Créer une activité"** (or edit an existing one).
*   **Expected Result**:
    *   **Global Layout**: Side-by-side layout (Sidebar + Content).
    *   **Sticky Header**:
        *   **Left**: Title "Créer une activité".
        *   **Right**: The button group (Status, Preview, Save) is **aligned to the far right**.
        *   **Status**: "Hors ligne" (grey pill) or "En ligne".
        *   **Preview**: "Prévisualiser" button (White with Orange border).
        *   **Save**: "Enregistré" button (Green background).

## 2. General Info Section (New Grid Layout)
*   **Step**: Check the "Informations générales" card.
    *   **Verify**: Title has subtitle "Les informations essentielles pour catégoriser votre activité".
    *   **Verify**: **Nom de l'activité** is full width.
    *   **Verify**: **Catégorie** (Left) and **Type d'événement** (Right) are on the same row (50/50).
    *   **Verify**: **Public visé** (Left) and **Thématiques** (Right) are on the same row.
    *   **Verify**: **Événements** (Left) and **Émotions** (Right) are on the same row.
    *   **Verify**: **Activités à associer** is full width.
*   **Step**: Select items in "Public visé" or "Thématiques".
    *   **Verify**: The selected tags have a **Light Orange background** (#FFF5EB) with an Orange "X".
*   **Step**: Scroll to "Ajouter des co-organisateurs".
    *   **Verify**: The title is Orange.
    *   **Verify**: Click "Ajouter un co-organisateur" (Orange Button). A new row appears with:
        *   Input "Nom de l'organisation" (Left).
        *   Text "Son rôle" (Center).
        *   Select "Co-organisateur/Partenaire/Sponsor" (Right).
        *   "X" button to remove.

## 3. Presentation Section
*   **Step**: Check the "Présentation" card.
    *   **Verify**: Title has subtitle "Détaillez votre activité...".
    *   **Verify**: **Description** editor has a clean grey border and character counter.
    *   **Verify**: **Image à la une** has a "Ajouter une image" button with a **dashed border**.
    *   **Verify**: **Galerie d'images** follows the same style.
    *   **Verify**: **Réseaux sociaux** allows adding links with icons via the "Ajouter un réseau social" button.

## 4. Navigation Test
*   **Step**: Click on the **"Localisation"** link in the inner sidebar.
    *   **Verify**: The page **smooth scrolls** to the Location section card.
    *   **Verify**: The "Localisation" link becomes **active**.
*   **Step**: Scroll manually.
    *   **Verify**: The sidebar links update their active state (**ScrollSpy**).

## 5. Section Logic Verification
*   **Step**: Test the dynamic toggles (Online/Physical, Recurring/Manual, Ticket Link/Internal).
    *   **Verify**: Fields appear/disappear correctly within their respective cards without breaking the layout.
    *   **Verify**: Inputs have the new Airbnb style (uppercase labels, black focus border).

## 6. Completion Gauge Test
*   **Step**: Check the sidebar widget.
    *   **Verify**: The progress bar updates as you fill fields.

## 7. Submission
*   **Step**: Click **"Enregistré"** in the sticky header.
    *   **Verify**: The form submits and saves data correctly.
