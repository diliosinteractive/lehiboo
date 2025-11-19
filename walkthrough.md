# Verification Walkthrough: Refactored Event Form (Profile Style)

This guide outlines the steps to verify the new "One Page" event creation/editing form, which now matches the "Mon Profil" design.

## 1. Access & Layout Verification
*   **Step**: Login as a Partner and navigate to **"Créer une activité"** (or edit an existing one).
*   **Expected Result**:
    *   The page loads with a **clean, card-based layout** similar to the Profile page.
    *   **Left Sidebar**: 
        *   Shows the event thumbnail and title at the top.
        *   Navigation links with icons (Document, Image, Pin, Calendar, Ticket, Globe).
        *   **Completion Widget**: A progress bar is now located in the sidebar (not the footer).
    *   **Sticky Header**: A bar at the top of the form content with "Prévisualiser", "Enregistrer", and "Mettre en ligne".

## 2. Navigation Test
*   **Step**: Click on the **"Localisation"** link in the left sidebar.
    *   **Verify**: The page **smooth scrolls** to the Location section card.
    *   **Verify**: The "Localisation" link becomes **active** (highlighted).
*   **Step**: Scroll manually up and down the page.
    *   **Verify**: The sidebar links automatically update their active state (**ScrollSpy**).

## 3. Section Logic Verification

### A. Location Section
*   **Step**: In "Où se déroule l'événement ?", select **"En ligne"**.
    *   **Verify**: The physical address fields (Map, Address, Venue) **disappear**.
    *   **Verify**: The **"Lien de l'événement (URL)"** field **appears**.
*   **Step**: Select **"Dans un lieu physique"**.
    *   **Verify**: The address fields reappear.

### B. Calendar Section
*   **Step**: Select **"Récurrent"**.
    *   **Verify**: The recurrence settings (Frequency, Interval, Days of week) **appear**.
    *   **Verify**: The manual date list **disappears**.
*   **Step**: Select **"Date unique / Ponctuel"**.
    *   **Verify**: The manual date picker list **appears**.

### C. Ticketing Section
*   **Step**: Select **"Utiliser un lien externe"**.
    *   **Verify**: The **"Lien URL de la billetterie"** field appears.
*   **Step**: Select **"Créer une liste d'inscription"**.
    *   **Verify**: The internal ticket builder (Name, Price, Quantity) appears.

### D. Publication Section
*   **Step**: In "Visibilité", select **"Protégé par mot de passe"**.
    *   **Verify**: The **"Définir un mot de passe"** field slides down.
*   **Step**: Select **"Public"**.
    *   **Verify**: The password field slides up/hides.

## 4. Completion Gauge Test
*   **Step**: Start with an empty form.
    *   **Verify**: The gauge in the **sidebar** shows a low percentage.
    *   **Verify**: The **"Mettre en ligne"** button in the sticky header is **disabled**.
*   **Step**: Fill in required fields.
*   **Verify**: The gauge percentage **increases**.
*   **Verify**: Once sufficient fields are filled, the **"Mettre en ligne"** button becomes **enabled** (orange).

## 5. Submission
*   **Step**: Click **"Enregistrer"** in the sticky header.
    *   **Verify**: The page reloads (or AJAX saves) and the data is persisted.
