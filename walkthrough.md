# Verification Walkthrough: Refactored Event Form

This guide outlines the steps to verify the new "One Page" event creation/editing form in the Partner Backoffice.

## 1. Access & Layout Verification
*   **Step**: Login as a Partner and navigate to **"Créer une activité"** (or edit an existing one).
*   **Expected Result**:
    *   The page loads with the **new multi-block layout**.
    *   **Left Sidebar**: Displays navigation links (Informations générales, Présentation, Localisation, etc.).
    *   **Sticky Footer**: A fixed bar at the bottom with "Aperçu", "Enregistrer", "Mettre en ligne" and a completion gauge.

## 2. Navigation Test
*   **Step**: Click on the **"Localisation"** link in the left sidebar.
    *   **Verify**: The page **smooth scrolls** to the Location section.
    *   **Verify**: The "Localisation" link becomes **active** (highlighted).
*   **Step**: Scroll manually up and down the page.
    *   **Verify**: The sidebar links automatically update their active state (**ScrollSpy**) corresponding to the visible section.

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
*   **Step**: Start with an empty form (or clear required fields like Title).
    *   **Verify**: The gauge in the footer shows a low percentage (e.g., 0% or 10%).
    *   **Verify**: The **"Mettre en ligne"** button is **disabled** (greyed out).
*   **Step**: Fill in required fields:
    *   Title ("Nom de l'événement")
    *   Category
    *   Description
    *   Featured Image
    *   Location/Online Link
*   **Verify**: The gauge percentage **increases** as you fill fields.
*   **Verify**: Once sufficient fields are filled (approx > 80%), the **"Mettre en ligne"** button becomes **enabled**.

## 5. Submission
*   **Step**: Click **"Enregistrer"**.
    *   **Verify**: The page reloads (or AJAX saves) and the data is persisted.
