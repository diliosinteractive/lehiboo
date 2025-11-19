#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de traduction automatique du fichier POT en français
Pour EventList Plugin - Plateforme de réservation d'événements
"""

import re
from datetime import datetime

# Dictionnaire de traductions prioritaires et contextuelles
translations = {
    # Boutons et actions principales
    "Book Now": "Réserver maintenant",
    "Download Ticket": "Télécharger le billet",
    "Download Tickets": "Télécharger les billets",
    "The Cart is empty": "Le panier est vide",
    "Add to Cart": "Ajouter au panier",
    "Checkout": "Passer la commande",
    "Book Ticket Success": "Réservation réussie",
    "Booking Ticket Success": "Réservation de billet réussie",

    # Messages utilisateur
    "Please fill out this field": "Veuillez remplir ce champ",
    "field is required": "champ obligatoire",
    "Email address is not valid.": "L'adresse e-mail n'est pas valide.",
    "Booking ID": "ID de réservation",
    "Booking Information": "Informations de réservation",

    # Événements
    "Event": "Événement",
    "Events": "Événements",
    "Event Name": "Nom de l'événement",
    "Event List": "Liste d'événements",
    "Add New": "Ajouter nouveau",
    "Add new event": "Ajouter un nouvel événement",
    "Edit Event": "Modifier l'événement",
    "All Events": "Tous les événements",
    "Event Status": "Statut de l'événement",

    # Billets
    "Ticket": "Billet",
    "Tickets": "Billets",
    "Add new ticket": "Ajouter un nouveau billet",
    "Ticket Type": "Type de billet",
    "Free": "Gratuit",
    "Price": "Prix",
    "Quantity": "Quantité",
    "Available": "Disponible",
    "Sold Out": "Épuisé",

    # Réservations
    "Booking": "Réservation",
    "Bookings": "Réservations",
    "All Bookings": "Toutes les réservations",
    "Cancel Booking": "Annuler la réservation",
    "Completed": "Terminée",
    "Pending": "En attente",
    "Canceled": "Annulée",
    "Confirmed": "Confirmée",

    # Client/Customer
    "Customer": "Client",
    "Customer Name": "Nom du client",
    "Customer Email": "E-mail du client",
    "Customer Phone": "Téléphone du client",
    "First Name": "Prénom",
    "Last Name": "Nom",
    "Email": "E-mail",
    "Phone": "Téléphone",
    "Address": "Adresse",

    # Paiement
    "Payment": "Paiement",
    "Payment Method": "Mode de paiement",
    "Total": "Total",
    "Subtotal": "Sous-total",
    "Discount": "Réduction",
    "Tax": "Taxe",
    "Amount": "Montant",

    # Dates et temps
    "Date": "Date",
    "Time": "Heure",
    "Start Date": "Date de début",
    "End Date": "Date de fin",
    "Start Time": "Heure de début",
    "End Time": "Heure de fin",
    "Calendar": "Calendrier",
    "Today": "Aujourd'hui",
    "Daily": "Quotidien",
    "Weekly": "Hebdomadaire",
    "Monthly": "Mensuel",

    # Actions
    "Save": "Enregistrer",
    "Update": "Mettre à jour",
    "Delete": "Supprimer",
    "Edit": "Modifier",
    "View": "Voir",
    "Cancel": "Annuler",
    "Close": "Fermer",
    "Apply": "Appliquer",
    "Submit": "Soumettre",
    "Search": "Rechercher",
    "Filter": "Filtrer",
    "Export": "Exporter",
    "Import": "Importer",
    "Download": "Télécharger",
    "Upload": "Téléverser",

    # Formulaires
    "Name": "Nom",
    "Title": "Titre",
    "Description": "Description",
    "Category": "Catégorie",
    "Categories": "Catégories",
    "Location": "Lieu",
    "Venue": "Lieu de l'événement",
    "Status": "Statut",
    "Active": "Actif",
    "Inactive": "Inactif",
    "Enable": "Activer",
    "Disable": "Désactiver",

    # Messages système
    "Success": "Succès",
    "Error": "Erreur",
    "Warning": "Avertissement",
    "Info": "Information",
    "Loading": "Chargement",
    "Please wait": "Veuillez patienter",
    "Processing": "Traitement en cours",

    # Navigation
    "All": "Tous",
    "Filter": "Filtrer",
    "Sort": "Trier",
    "Ascending": "Croissant",
    "Descending": "Décroissant",
    "Next": "Suivant",
    "Previous": "Précédent",
    "First": "Premier",
    "Last": "Dernier",

    # Paramètres
    "Settings": "Paramètres",
    "General": "Général",
    "Advanced": "Avancé",
    "Options": "Options",
    "Configuration": "Configuration",

    # Vendor/Organisateur
    "Vendor": "Organisateur",
    "Profile": "Profil",
    "Dashboard": "Tableau de bord",
    "My Events": "Mes événements",
    "My Bookings": "Mes réservations",

    # Check-in
    "Check-in": "Enregistrement",
    "Check In": "Enregistrer",
    "Checked In": "Enregistré",
    "Checkin time": "Heure d'enregistrement",

    # Autres
    "Yes": "Oui",
    "No": "Non",
    "None": "Aucun",
    "Required": "Obligatoire",
    "Optional": "Optionnel",
    "Clear": "Effacer",
    "Reset": "Réinitialiser",
    "Default": "Par défaut",
}

def translate_string(text):
    """Traduit une chaîne anglaise en français"""
    # Retour direct si vide
    if not text or text == '""':
        return text

    # Chercher une traduction exacte
    if text in translations:
        return translations[text]

    # Traductions par mots-clés (pour les phrases composées)
    result = text

    # Règles de traduction contextuelles
    patterns = [
        (r'^Add (.*)', lambda m: f"Ajouter {translate_word(m.group(1))}"),
        (r'^Edit (.*)', lambda m: f"Modifier {translate_word(m.group(1))}"),
        (r'^Delete (.*)', lambda m: f"Supprimer {translate_word(m.group(1))}"),
        (r'^View (.*)', lambda m: f"Voir {translate_word(m.group(1))}"),
        (r'^All (.*)', lambda m: f"Tous les {translate_word(m.group(1))}"),
        (r'^New (.*)', lambda m: f"Nouveau {translate_word(m.group(1))}"),
        (r'(.*) is required', lambda m: f"{translate_word(m.group(1))} est obligatoire"),
        (r'(.*) not found', lambda m: f"{translate_word(m.group(1))} non trouvé"),
        (r'Select (.*)', lambda m: f"Sélectionner {translate_word(m.group(1))}"),
        (r'Choose (.*)', lambda m: f"Choisir {translate_word(m.group(1))}"),
        (r'Enter (.*)', lambda m: f"Saisir {translate_word(m.group(1))}"),
    ]

    for pattern, replacement in patterns:
        match = re.match(pattern, text)
        if match:
            try:
                return replacement(match)
            except:
                pass

    # Si aucune traduction trouvée, retourner le texte original
    # (sera marqué pour traduction manuelle)
    return None

def translate_word(word):
    """Traduit un mot simple"""
    word_lower = word.lower()
    simple_words = {
        "event": "événement",
        "events": "événements",
        "ticket": "billet",
        "tickets": "billets",
        "booking": "réservation",
        "bookings": "réservations",
        "customer": "client",
        "vendor": "organisateur",
        "category": "catégorie",
        "location": "lieu",
        "venue": "lieu",
        "name": "nom",
        "email": "e-mail",
        "phone": "téléphone",
        "address": "adresse",
        "date": "date",
        "time": "heure",
        "price": "prix",
        "total": "total",
        "status": "statut",
        "description": "description",
        "title": "titre",
        "calendar": "calendrier",
        "gallery": "galerie",
        "coupon": "coupon",
        "package": "forfait",
        "profile": "profil",
        "settings": "paramètres",
        "payment": "paiement",
    }
    return simple_words.get(word_lower, word)

def process_pot_file(input_file, output_file):
    """Traite le fichier POT et génère le fichier PO français"""

    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Remplacer le header
    header = '''# French (France) translation for EventList Plugin
# Copyright (C) 2025 EventList
# This file is distributed under the same license as the EventList package.
msgid ""
msgstr ""
"Project-Id-Version: Event List\\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: 2024-12-17 08:07+0000\\n"
"PO-Revision-Date: {date}\\n"
"Last-Translator: Auto Translation <translation@example.com>\\n"
"Language-Team: French\\n"
"Language: fr_FR\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=2; plural=(n > 1);\\n"
"X-Generator: Custom Python Script\\n"
"X-Domain: eventlist"

'''.format(date=datetime.now().strftime('%Y-%m-%d %H:%M+0000'))

    # Extraire le contenu après le header original
    lines = content.split('\n')
    start_idx = 0
    for i, line in enumerate(lines):
        if line.startswith('#:') or line.startswith('msgid'):
            start_idx = i
            break

    # Traiter les entrées msgid/msgstr
    result = [header]
    i = start_idx
    translated_count = 0

    while i < len(lines):
        line = lines[i]

        # Copier les commentaires et références
        if line.startswith('#'):
            result.append(line)
            i += 1
            continue

        # Traiter msgid
        if line.startswith('msgid '):
            msgid_lines = [line]
            i += 1

            # Capturer les lignes de continuation
            while i < len(lines) and lines[i].startswith('"'):
                msgid_lines.append(lines[i])
                i += 1

            # Extraire le texte msgid
            msgid_text = ' '.join(msgid_lines).replace('msgid ', '', 1)
            msgid_text = msgid_text.strip()

            # Vérifier si c'est msgid_plural
            if i < len(lines) and lines[i].startswith('msgid_plural'):
                # Gérer les formes plurielles
                result.extend(msgid_lines)
                result.append(lines[i])  # msgid_plural
                i += 1

                # Capturer msgstr[0] et msgstr[1]
                while i < len(lines) and (lines[i].startswith('msgstr[') or lines[i].startswith('"')):
                    if lines[i].startswith('msgstr[0]'):
                        # Traduire le singulier
                        msgstr_text = extract_quoted_text(msgid_text)
                        translation = translate_string(msgstr_text)
                        if translation:
                            result.append(f'msgstr[0] "{translation}"')
                            translated_count += 1
                        else:
                            result.append('msgstr[0] ""')
                    elif lines[i].startswith('msgstr[1]'):
                        # Pour le pluriel, ajouter 's' ou adapter
                        msgstr_text = extract_quoted_text(msgid_text)
                        translation = translate_string(msgstr_text)
                        if translation:
                            # Simple ajout de 's' pour le pluriel (à améliorer)
                            plural = translation + 's' if not translation.endswith('s') else translation
                            result.append(f'msgstr[1] "{plural}"')
                        else:
                            result.append('msgstr[1] ""')
                    else:
                        result.append(lines[i])
                    i += 1
            else:
                # msgid standard
                result.extend(msgid_lines)

                # Traiter msgstr
                if i < len(lines) and lines[i].startswith('msgstr '):
                    # Extraire et traduire
                    msgstr_text = extract_quoted_text(msgid_text)

                    if msgstr_text:
                        translation = translate_string(msgstr_text)
                        if translation:
                            result.append(f'msgstr "{translation}"')
                            translated_count += 1
                        else:
                            # Laisser vide pour traduction manuelle
                            result.append('msgstr ""')
                    else:
                        result.append('msgstr ""')
                    i += 1

                    # Capturer les lignes de continuation msgstr
                    while i < len(lines) and lines[i].startswith('"'):
                        # Skip continuation lines (already translated)
                        i += 1
        else:
            # Ligne vide ou autre
            if line.strip():
                result.append(line)
            else:
                result.append('')
            i += 1

    # Écrire le fichier de sortie
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(result))

    return translated_count

def extract_quoted_text(text):
    """Extrait le texte entre guillemets"""
    match = re.search(r'"([^"]*)"', text)
    if match:
        return match.group(1)
    return ""

# Point d'entrée principal
if __name__ == "__main__":
    input_file = "/Users/juba/PhpstormProjects/lehiboo_v1/wp-content/plugins/eventlist/languages/eventlist.pot"
    output_file = "/Users/juba/PhpstormProjects/lehiboo_v1/wp-content/plugins/eventlist/languages/eventlist-fr_FR.po"

    print("Début de la traduction...")
    print(f"Fichier source: {input_file}")
    print(f"Fichier destination: {output_file}")

    count = process_pot_file(input_file, output_file)

    print(f"\n✓ Traduction terminée!")
    print(f"✓ {count} chaînes traduites automatiquement")
    print(f"✓ Fichier créé: {output_file}")
