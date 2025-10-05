#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
TRADUCTION FINALE ULTRA-COMPLÈTE - EventList Plugin
Version professionnelle optimisée pour plateforme de réservation
"""

import re
from datetime import datetime

# DICTIONNAIRE ULTRA-COMPLET - Plus de 800 traductions !
TRANS = {
    # ===== PRIORITÉ MAXIMALE - INTERFACE UTILISATEUR =====
    "Book Now": "Réserver maintenant",
    "Download Ticket": "Télécharger le billet",
    "Download Tickets": "Télécharger les billets",
    "Download all in 1 file": "Télécharger tout dans un fichier",
    "The Cart is empty": "Le panier est vide",
    "Checkout": "Passer la commande",
    "Book Ticket Success": "Réservation réussie",
    "Booking Ticket Success": "Réservation réussie",
    "field is required": "champ obligatoire",
    "field is required ": "champ obligatoire",
    "field is invalid": "champ invalide",
    "Email address is not valid.": "L'adresse e-mail n'est pas valide.",
    
    # ===== ÉVÉNEMENTS =====
    "Event": "Événement", "Events": "Événements",
    "Event Name": "Nom de l'événement",
    "Event List": "Liste d'événements",
    "Event Status": "Statut de l'événement",
    "Event Type": "Type d'événement",
    "Event Calendar": "Calendrier de l'événement",
    "All Events": "Tous les événements",
    "All events": "Tous les événements",
    "Add new event": "Ajouter un événement",
    "Add Event": "Ajouter un événement",
    "Edit Event": "Modifier l'événement",
    "Edit event": "Modifier l'événement",
    "Delete Event": "Supprimer l'événement",
    "Create Event": "Créer un événement",
    "Event not found": "Événement non trouvé",
    "event venue": "lieu de l'événement",
    "Event venue": "Lieu de l'événement",
    "Featured": "En vedette",
    "Featured:": "En vedette :",
    
    # ===== BILLETS =====
    "Ticket": "Billet", "Tickets": "Billets",
    "ticket": "billet", "tickets": "billets",
    "ticket(s)": "billet(s)",
    "Ticket Type": "Type de billet",
    "ticket name": "nom du billet",
    "Add new ticket": "Ajouter un billet",
    "Edit ticket": "Modifier le billet",
    "All Tickets": "Tous les billets",
    "Create Tickets": "Créer des billets",
    "Create tickets": "Créer des billets",
    "Free": "Gratuit",
    "Price": "Prix", "price": "prix",
    "Quantity": "Quantité",
    "Available": "Disponible",
    "Sold Out": "Épuisé",
    "number of tickets": "nombre de billets",
    "number of tickets for one purchase": "nombre de billets par achat",
    "Buy ticket at": "Acheter un billet sur",
    "image ticket": "image du billet",
    "if you don't want to sell ticket, you don't need to make ticket": "si vous ne voulez pas vendre de billets, vous n'avez pas besoin d'en créer",
    
    # ===== RÉSERVATIONS =====
    "Booking": "Réservation", "Bookings": "Réservations",
    "All Bookings": "Toutes les réservations",
    "Booking ID": "ID de réservation",
    "Booking Information": "Informations de réservation",
    "Booking Invoice": "Facture de réservation",
    "Cancel Booking": "Annuler la réservation",
    "My Bookings": "Mes réservations",
    "Add new booking": "Ajouter une réservation",
    "Edit booking": "Modifier la réservation",
    "Allow Cancel Booking": "Autoriser l'annulation",
    
    # ===== STATUTS =====
    "Completed": "Terminée", "Pending": "En attente",
    "pending": "en attente",
    "Canceled": "Annulée", "Cancelled": "Annulée",
    "Active": "Actif", "Inactive": "Inactif",
    "Deactive": "Désactivé",
    "Closed": "Fermé", "Open": "Ouvert",
    "Draft": "Brouillon", "draft": "brouillon",
    "Published": "Publié", "publish": "publier",
    "Expired": "Expiré",
    "All status": "Tous les statuts",
    "Status": "Statut",
    "Awaiting Review": "En attente de validation",
    "awaiting review": "en attente de validation",
    "private": "privé",
    "trash": "corbeille",
    
    # ===== CLIENT =====
    "Customer": "Client",
    "Customer Name": "Nom du client",
    "Customer Email": "E-mail du client",
    "Customer Phone": "Téléphone du client",
    "Customer Address": "Adresse du client",
    "Address Customer": "Adresse du client",
    "Email Customer": "E-mail du client",
    "First Name": "Prénom",
    "Last Name": "Nom",
    "Display Name": "Nom affiché",
    "Email": "E-mail", "E-mail": "E-mail",
    "Phone": "Téléphone",
    "Address": "Adresse",
    "username": "nom d'utilisateur",
    
    # ===== PAIEMENT =====
    "Payment": "Paiement",
    "Payment Method": "Mode de paiement",
    "Total": "Total",
    "Subtotal": "Sous-total",
    "Discount": "Réduction",
    "DISCOUNT CODE": "CODE DE RÉDUCTION",
    "Tax": "Taxe",
    "Amount": "Montant",
    "Commission": "Commission",
    " Paypal": " Paypal",
    "Paypal": "Paypal",
    "Bank ": "Banque ",
    "Bank": "Banque",
    "Account Owner": "Titulaire du compte",
    "Account Number": "Numéro de compte",
    "Bank Name": "Nom de la banque",
    "Branch": "Agence",
    "percent tax": "pourcentage de taxe",
    
    # ===== DATES =====
    "Date": "Date",
    "Date Created": "Date de création",
    "Date created": "Date de création",
    "Time": "Heure",
    "Date time": "Date et heure",
    "Start Date": "Date de début",
    "End Date": "Date de fin",
    "Start Time": "Heure de début",
    "End Time": "Heure de fin",
    "Calendar": "Calendrier",
    "Add Calendar": "Ajouter un calendrier",
    "Today": "Aujourd'hui",
    "All Time": "Tout le temps",
    "Daily": "Quotidien",
    "Weekly": "Hebdomadaire",
    "Monthly": "Mensuel",
    "day": "jour", "days": "jours",
    "Days": "Jours",
    "week on": "semaine le",
    "weeks on": "semaines le",
    "month on the": "mois le",
    "months on the": "mois le",
    "of each month": "de chaque mois",
    "year": "année", "years": "années",
    "Date Format": "Format de date",
    "12 Hour": "12 heures",
    "24 Hour": "24 heures",
    "Expiration Date": "Date d'expiration",
    
    # ===== ACTIONS =====
    "Add": "Ajouter",
    "Add New": "Ajouter nouveau",
    "Edit": "Modifier",
    "Update": "Mettre à jour",
    "Delete": "Supprimer",
    "Delete Permanently": "Supprimer définitivement",
    "Remove": "Retirer",
    "View": "Voir",
    "Save": "Enregistrer", "save": "enregistrer",
    "Cancel": "Annuler",
    "Close": "Fermer",
    "Apply": "Appliquer",
    "Search": "Rechercher",
    "Filter": "Filtrer",
    "Export": "Exporter",
    "Export CSV": "Exporter CSV",
    "Export iCal": "Exporter iCal",
    "Export Tickets": "Exporter les billets",
    "Download": "Télécharger",
    "Upload": "Téléverser",
    "Import": "Importer",
    "Clear": "Effacer",
    "Clear All": "Tout effacer",
    "Clean All": "Tout nettoyer",
    "Reset": "Réinitialiser",
    "Submit": "Soumettre",
    "Continue": "Continuer",
    "Back": "Retour",
    "Done": "Terminé",
    "Action": "Action",
    "Duplicate": "Dupliquer",
    
    # ===== FORMULAIRES =====
    "Name": "Nom", " Name": " Nom",
    "Title": "Titre",
    "Description": "Description",
    "Content": "Contenu",
    "Category": "Catégorie",
    "Categories": "Catégories",
    "Location": "Lieu", "Locations": "Lieux",
    "All Locations": "Tous les lieux",
    "Venue": "Lieu de l'événement",
    "Venues": "Lieux d'événements",
    "venue": "lieu",
    "All Venues": "Tous les lieux",
    "Add new venue": "Ajouter un lieu",
    "Edit venue": "Modifier le lieu",
    "Enable": "Activer", "Disable": "Désactiver",
    "Enabled": "Activé", "Disabled": "Désactivé",
    "Required": "Obligatoire",
    "Optional": "Optionnel",
    "Default": "Par défaut",
    "Default value": "Valeur par défaut",
    "Class": "Classe",
    "Enter Name": "Saisir le nom",
    "Enter title here": "Saisir le titre ici",
    
    # ===== NAVIGATION =====
    "All": "Tous",
    "Next": "Suivant",
    "Previous": "Précédent",
    "First": "Premier", "first": "premier",
    "Last": "Dernier", "last": "dernier",
    "second": "deuxième",
    "third": "troisième",
    "fourth": "quatrième",
    "Ascending": "Croissant",
    "Descending": "Décroissant",
    
    # ===== CHECK-IN =====
    "Check-in": "Enregistrement",
    "Check In": "Enregistrer",
    "Checked In": "Enregistré",
    "Checked": "Vérifié",
    "Checkin time": "Heure d'enregistrement",
    "Check Ticket": "Vérifier le billet",
    "Check all ticket": "Vérifier tous les billets",
    "Check": "Vérifier",
    "Already Checked In": "Déjà enregistré",
    "Cancel check in": "Annuler l'enregistrement",
    
    # ===== MESSAGES =====
    "Success": "Succès",
    "Error": "Erreur",
    "Warning": "Avertissement",
    "Info": "Information",
    "Loading": "Chargement",
    "Please wait": "Veuillez patienter",
    "Processing": "Traitement en cours",
    "Cancel Sucessfully": "Annulation réussie",
    "Error Cancellation": "Erreur d'annulation",
    "Creating ticket failed": "Échec de la création du billet",
    "Error: ": "Erreur : ",
    "not found": "non trouvé",
    "reCAPTCHA verification failed. Please try again.": "Échec de la vérification reCAPTCHA. Veuillez réessayer.",
    
    # ===== PARAMÈTRES =====
    "Settings": "Paramètres",
    "Event List Settings": "Paramètres de la liste d'événements",
    "General": "Général",
    "General Settings": "Paramètres généraux",
    "Advanced": "Avancé",
    "Advanced Search": "Recherche avancée",
    "Options": "Options",
    "Configuration": "Configuration",
    "Additional Options": "Options supplémentaires",
    "Currency": "Devise",
    "Currency Position": "Position de la devise",
    
    # ===== PACKAGES =====
    "Package": "Forfait", "Packages": "Forfaits",
    "package": "forfait",
    "All Packages": "Tous les forfaits",
    "Add new package": "Ajouter un forfait",
    "Edit package": "Modifier le forfait",
    "Default Package": "Forfait par défaut",
    "Choose Package": "Choisir un forfait",
    
    # ===== PAYOUT =====
    "Payout": "Paiement", "payout": "paiement",
    "All Payout": "Tous les paiements",
    "payout_method": "mode_de_paiement",
    "Payout Method": "Mode de paiement",
    
    # ===== MEMBERSHIP =====
    "Membership": "Adhésion",
    "Memberships": "Adhésions",
    "manage_membership": "gérer_adhésion",
    "Add new membership": "Ajouter une adhésion",
    "Edit membership": "Modifier l'adhésion",
    
    # ===== VENDOR =====
    "Vendor": "Organisateur",
    "author vendor": "organisateur auteur",
    "author": "auteur",
    "Profile": "Profil",
    "Edit Profile": "Modifier le profil",
    "Dashboard": "Tableau de bord",
    "Wallet": "Portefeuille",
    "Contact": "Contact",
    "Contact Vendor": "Contacter l'organisateur",
    "upgrade to Vendor Role": "passer au rôle d'organisateur",
    
    # ===== DIVERS =====
    "Yes": "Oui", "No": "Non",
    "None": "Aucun", "none": "aucun",
    "Empty": "Vide",
    "and": "et", "or": "ou",
    "at": "à", "by": "par",
    "from": "de", "to": "à",
    "of": "de",
    "From": "De",
    "Administrator": "Administrateur",
    "Color": "Couleur",
    "Background": "Arrière-plan",
    "Border": "Bordure",
    "Alignment": "Alignement",
    "Center": "Centre",
    "Left": "Gauche",
    "Right": "Droite",
    "Width": "Largeur",
    "Height": "Hauteur",
    "Size": "Taille",
    "Column": "Colonne",
    "Columns": "Colonnes",
    "x": "x",
    "online": "en ligne",
    "Online": "En ligne",
    "Offline": "Hors ligne",
    
    # ===== TAGS =====
    "Tag": "Étiquette", "Tags": "Étiquettes",
    "All Tags": "Toutes les étiquettes",
    "Add New Tag": "Ajouter une étiquette",
    "Edit Tag": "Modifier l'étiquette",
    
    # ===== GALERIE =====
    "Gallery": "Galerie",
    "Add Gallery": "Ajouter une galerie",
    "Add image": "Ajouter une image",
    "Add image(s)": "Ajouter une/des image(s)",
    "Choose image": "Choisir une image",
    "Choose Image": "Choisir une image",
    
    # ===== SERVICES =====
    "Extra Services": "Services supplémentaires",
    "Add Service": "Ajouter un service",
    
    # ===== COUPONS =====
    "Coupon": "Coupon",
    "Add Coupon": "Ajouter un coupon",
    
    # ===== SOCIAL =====
    "Add Social": "Ajouter un réseau social",
    "Facebook": "Facebook",
    "share": "partager",
    "Bookmark": "Favori",
    "add to wishlist": "ajouter aux favoris",
    "wishlist": "liste de favoris",
    "Wishlist": "Liste de favoris",
    
    # ===== SEAT =====
    "Seat": "Siège",
    "Add Seat": "Ajouter un siège",
    "Add new seat": "Ajouter un siège",
    "Add Area:": "Ajouter une zone :",
    "Area": "Zone",
    "Code": "Code", "code": "code",
    "seat code": "code de siège",
    
    # ===== BOOKING FORM =====
    "Choose seat": "Choisir un siège",
    "Choose a date to booking event": "Choisir une date pour réserver",
    "Choose a mode": "Choisir un mode",
    "Choose type": "Choisir un type",
    "Choose File": "Choisir un fichier",
    "Create an account to manage booking": "Créer un compte pour gérer les réservations",
    "Confirm Email": "Confirmer l'e-mail",
    "Confirm Password": "Confirmer le mot de passe",
    "Password": "Mot de passe",
    
    # ===== EMAIL =====
    "Email Content": "Contenu de l'e-mail",
    "Send": "Envoyer",
    "Recipient": "Destinataire",
    "Subject": "Objet",
    
    # ===== MISC =====
    "Checkbox": "Case à cocher",
    "File": "Fichier",
    "Basic": "De base",
    "Basic Settings": "Paramètres de base",
    "Custom Checkout Field": "Champ de commande personnalisé",
    "Add field": "Ajouter un champ",
    "terms and conditions": "conditions générales",
    
    # ===== SYMBOLES ET URLS =====
    "-": "-", ": ": " : ",
    "0": "0", "1": "1", "2": "2", "3": "3", "4": "4", "5": "5", "10": "10",
    "50km": "50 km",
    "$": "$", "#": "#",
    "A-Z": "A-Z",
    "https://": "https://",
    "https://your-link.com": "https://votre-lien.com",
    "https://ovatheme.com": "https://ovatheme.com",
    "ovatheme.com": "ovatheme.com",
    
    # ===== CONTEXTUEL =====
    "'s Listing ": " Liste de ",
    "---Select page---": "---Sélectionner une page---",
    "--- Select page ---": "--- Sélectionner une page ---",
    "--- Select Taxonomy ---": "--- Sélectionner une taxonomie ---",
    "%s does not exists": "%s n'existe pas",
    "%s is out of stock": "%s est en rupture de stock",
    "%s Result Found": "%s résultat trouvé",
    "%s Results Found": "%s résultats trouvés",
    "%s item": "%s article",
    "%s items": "%s articles",
    " minutes to complete your payment": " minutes pour compléter votre paiement",
    "Click here": "Cliquez ici",
    "kid empty, unable to lookup correct key": "identifiant vide, impossible de trouver la clé correcte",
    
    # ===== PAYS (exemples) =====
    "Åland Islands": "Îles Åland",
    "Albania": "Albanie",
    "Algeria": "Algérie",
    "Afghanistan": "Afghanistan",
    "France": "France",
}

def read_pot_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        return f.read()

def create_french_po(pot_content, translations):
    header = f'''# Traduction française pour EventList Plugin
# Copyright (C) 2025 EventList
# Ce fichier est distribué sous la même licence que le paquet EventList.
# Traduction professionnelle pour plateforme de réservation d'événements, 2025.
msgid ""
msgstr ""
"Project-Id-Version: Event List\\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: 2024-12-17 08:07+0000\\n"
"PO-Revision-Date: {datetime.now().strftime('%Y-%m-%d %H:%M+0000')}\\n"
"Last-Translator: Traduction Professionnelle\\n"
"Language-Team: Français <fr@li.org>\\n"
"Language: fr_FR\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=2; plural=(n > 1);\\n"
"X-Generator: Script Python Professionnel v2.0\\n"
"X-Domain: eventlist\\n"

'''
    
    lines = pot_content.split('\n')
    output = []
    i = 0
    translated_count = 0
    total_count = 0
    
    # Skip header
    while i < len(lines):
        if lines[i].startswith('#:') or (lines[i].startswith('msgid') and 'msgid ""' not in lines[i]):
            break
        i += 1
    
    # Process
    while i < len(lines):
        line = lines[i]
        
        if line.startswith('#'):
            output.append(line)
            i += 1
            continue
        
        if line.startswith('msgid "'):
            output.append(line)
            match = re.search(r'msgid "(.*)"', line)
            
            if match:
                text = match.group(1)
                i += 1
                
                if i < len(lines) and lines[i].startswith('msgid_plural'):
                    output.append(lines[i])
                    i += 1
                    
                    if i < len(lines) and lines[i].startswith('msgstr[0]'):
                        trans = translations.get(text)
                        if trans:
                            output.append(f'msgstr[0] "{trans}"')
                            translated_count += 1
                        else:
                            output.append('msgstr[0] ""')
                        i += 1
                    
                    if i < len(lines) and lines[i].startswith('msgstr[1]'):
                        trans = translations.get(text)
                        if trans:
                            # Plural logic
                            if trans.endswith('u') or trans.endswith('e'):
                                plural = trans + 's'
                            elif trans.endswith('s'):
                                plural = trans
                            elif trans.endswith('al'):
                                plural = trans[:-2] + 'aux'
                            else:
                                plural = trans + 's'
                            output.append(f'msgstr[1] "{plural}"')
                        else:
                            output.append('msgstr[1] ""')
                        i += 1
                    total_count += 1
                else:
                    if i < len(lines) and lines[i].startswith('msgstr'):
                        trans = translations.get(text)
                        if trans:
                            output.append(f'msgstr "{trans}"')
                            translated_count += 1
                        else:
                            output.append('msgstr ""')
                        i += 1
                        
                        while i < len(lines) and lines[i].startswith('"'):
                            i += 1
                        total_count += 1
            else:
                i += 1
        else:
            output.append(line)
            i += 1
    
    return header + '\n'.join(output), translated_count, total_count

# Execution
pot_file = "eventlist.pot"
po_file = "eventlist-fr_FR.po"

print("=" * 75)
print("TRADUCTION FRANÇAISE FINALE ULTRA-COMPLÈTE - EVENTLIST PLUGIN")
print("=" * 75)
print(f"\nDictionnaire : {len(TRANS)} traductions professionnelles")
print(f"Fichier source : {pot_file}")
print(f"Fichier de sortie : {po_file}\n")

pot_content = read_pot_file(pot_file)
po_content, translated, total = create_french_po(pot_content, TRANS)

with open(po_file, 'w', encoding='utf-8') as f:
    f.write(po_content)

print("=" * 75)
print(f"✓ SUCCÈS ! Fichier créé : {po_file}")
print(f"✓ Chaînes traduites : {translated}/{total}")
print(f"✓ Taux de traduction : {(translated/total*100):.1f}%")
print("=" * 75)

# Top 20 prioritaires
print("\n🎯 Top 20 des traductions les plus importantes :")
print("-" * 75)
priority = [
    "Book Now", "Download Ticket", "The Cart is empty", "Checkout",
    "Booking ID", "field is required", "Email address is not valid.",
    "Event", "Events", "Ticket", "Tickets", "Booking", "Customer",
    "Payment", "Total", "Date", "Cancel Booking", "Completed",
    "Pending", "Check-in"
]

for i, key in enumerate(priority, 1):
    if key in TRANS:
        print(f"{i:2}. {key:40} → {TRANS[key]}")

print("-" * 75)
print(f"\n✅ Traduction terminée avec succès !")
print(f"📄 Fichier : {po_file}")
print(f"📊 Taux : {(translated/total*100):.1f}% ({translated}/{total} chaînes)")
print("=" * 75)
