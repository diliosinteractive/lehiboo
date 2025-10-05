#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Traduction française complète pour EventList Plugin
Plateforme de réservation d'événements professionnelle
"""

import re
from datetime import datetime

# Dictionnaire TRÈS COMPLET de traductions professionnelles
TRANSLATIONS = {
    # ==================== PRIORITÉ MAXIMALE ====================
    # Boutons principaux
    "Book Now": "Réserver maintenant",
    "Download Ticket": "Télécharger le billet",
    "Download Tickets": "Télécharger les billets",
    "Download all in 1 file": "Télécharger tout dans un seul fichier",
    "Create Tickets": "Créer des billets",
    "Create Event": "Créer un événement",
    "Create tickets": "Créer des billets",
    
    # Messages panier/checkout
    "The Cart is empty": "Le panier est vide",
    "Checkout": "Passer la commande",
    "Cart": "Panier",
    "Cart page": "Page panier",
    
    # Messages succès/erreur
    "Book Ticket Success": "Réservation réussie",
    "Booking Ticket Success": "Réservation de billet réussie",
    "Booking ID": "ID de réservation",
    "Booking Information": "Informations de réservation",
    "Booking Invoice": "Facture de réservation",
    
    # Messages formulaires
    "field is required": "champ obligatoire",
    "field is required ": "champ obligatoire",
    "field is invalid": "champ invalide",
    "Email address is not valid.": "L'adresse e-mail n'est pas valide.",
    "Email is not exist. Please check surely you created an account with this email address.": "Cet e-mail n'existe pas. Veuillez vérifier que vous avez créé un compte avec cette adresse e-mail.",
    
    # ==================== ÉVÉNEMENTS ====================
    "Event": "Événement",
    "Events": "Événements",
    "Event Name": "Nom de l'événement",
    "Event List": "Liste d'événements",
    "Event Status": "Statut de l'événement",
    "Event Type": "Type d'événement",
    "Event Calendar": "Calendrier de l'événement",
    "Event Filter": "Filtre d'événement",
    "Event Near Me": "Événements près de moi",
    "Event Categories": "Catégories d'événements",
    "Event Category": "Catégorie d'événement",
    "All Events": "Tous les événements",
    "All events": "Tous les événements",
    "Add new event": "Ajouter un nouvel événement",
    "Add Event": "Ajouter un événement",
    "Edit Event": "Modifier l'événement",
    "Edit event": "Modifier l'événement",
    "Delete Event": "Supprimer l'événement",
    "Filter events": "Filtrer les événements",
    "Event not found": "Événement non trouvé",
    "event venue": "lieu de l'événement",
    "Event venue": "Lieu de l'événement",
    "Event Name and Category must be filled out": "Le nom de l'événement et la catégorie doivent être renseignés",
    
    # ==================== BILLETS ====================
    "Ticket": "Billet",
    "Tickets": "Billets",
    "Ticket Type": "Type de billet",
    "Add new ticket": "Ajouter un nouveau billet",
    "Edit ticket": "Modifier le billet",
    "All Tickets": "Tous les billets",
    "Filter ticket": "Filtrer les billets",
    "Buy ticket at": "Acheter un billet sur",
    "Free": "Gratuit",
    "Price": "Prix",
    "Quantity": "Quantité",
    "Available": "Disponible",
    "Sold Out": "Épuisé",
    "Ticket info": "Informations du billet",
    "Add Ticket and send mail success": "Billet ajouté et e-mail envoyé avec succès",
    
    # ==================== RÉSERVATIONS ====================
    "Booking": "Réservation",
    "Bookings": "Réservations",
    "All Bookings": "Toutes les réservations",
    "My Bookings": "Mes réservations",
    "Cancel Booking": "Annuler la réservation",
    "Allow Cancel Booking": "Autoriser l'annulation de réservation",
    "Booking Package": "Forfait de réservation",
    "Booking order": "Commande de réservation",
    "Add new booking": "Ajouter une nouvelle réservation",
    "Edit booking": "Modifier la réservation",
    "Filter bookings": "Filtrer les réservations",
    "Export Bookings": "Exporter les réservations",
    "Booking Cancellation  Settings": "Paramètres d'annulation de réservation",
    "Cancellation Booking": "Annulation de réservation",
    "Cancel booking before x days": "Annuler la réservation avant x jours",
    "Allow customers to cancel booking": "Autoriser les clients à annuler leur réservation",
    "Don't allow cancel booking": "Ne pas autoriser l'annulation de réservation",
    "Do you want to cancel booking ?": "Voulez-vous annuler la réservation ?",
    
    # ==================== STATUTS ====================
    "Completed": "Terminée",
    "Pending": "En attente",
    "Canceled": "Annulée",
    "Cancelled": "Annulée",
    "Active": "Actif",
    "Inactive": "Inactif",
    "Deactive": "Désactivé",
    "Closed": "Fermé",
    "Open": "Ouvert",
    "Draft": "Brouillon",
    "draft": "brouillon",
    "Published": "Publié",
    "Expired": "Expiré",
    "All status": "Tous les statuts",
    "Status": "Statut",
    "Awaiting Review": "En attente de validation",
    "awaiting review": "en attente de validation",
    
    # ==================== CLIENT/UTILISATEUR ====================
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
    "Email": "E-mail",
    "E-mail": "E-mail",
    "Phone": "Téléphone",
    "Address": "Adresse",
    "Full Address": "Adresse complète",
    "Enter a address in map": "Saisir une adresse sur la carte",
    "Edit Full Address": "Modifier l'adresse complète",
    " Edit Full Address": " Modifier l'adresse complète",
    "Enter a venue": "Saisir un lieu",
    
    # ==================== PAIEMENT ====================
    "Payment": "Paiement",
    "Payment Method": "Mode de paiement",
    "Total": "Total",
    "Subtotal": "Sous-total",
    "Discount": "Réduction",
    "DISCOUNT CODE": "CODE DE RÉDUCTION",
    "Discount code must have at least 5 characters": "Le code de réduction doit contenir au moins 5 caractères",
    "Discount Amount": "Montant de la réduction",
    "Enter Discount Code": "Saisir le code de réduction",
    "Tax": "Taxe",
    "Amount": "Montant",
    "Subtotal": "Sous-total",
    "Commission": "Commission",
    " Paypal": " Paypal",
    "Paypal": "Paypal",
    "Bank ": "Banque ",
    "Bank": "Banque",
    "Account Owner": "Titulaire du compte",
    "Account owner *": "Titulaire du compte *",
    "Account Owner: ": "Titulaire du compte : ",
    "Account Number": "Numéro de compte",
    "Account number *": "Numéro de compte *",
    "Account Number: ": "Numéro de compte : ",
    "Bank Name": "Nom de la banque",
    "Bank Name *": "Nom de la banque *",
    "Bank Name: ": "Nom de la banque : ",
    "Branch": "Agence",
    "Branch *": "Agence *",
    "Branch: ": "Agence : ",
    
    # ==================== DATES ET TEMPS ====================
    "Date": "Date",
    "Date Created": "Date de création",
    "Date created": "Date de création",
    "Date Created From": "Date de création de",
    "Time": "Heure",
    "Date time": "Date et heure",
    "Start Date": "Date de début",
    "End Date": "Date de fin",
    "Start Time": "Heure de début",
    "End Time": "Heure de fin",
    "Start Date:": "Date de début :",
    "End Date:": "Date de fin :",
    "Start Time:": "Heure de début :",
    "End Time:": "Heure de fin :",
    "Calendar": "Calendrier",
    "Add Calendar": "Ajouter un calendrier",
    "Today": "Aujourd'hui",
    "All Time": "Tout le temps",
    "Daily": "Quotidien",
    "Weekly": "Hebdomadaire",
    "Monthly": "Mensuel",
    "day": "jour",
    "days": "jours",
    "Days": "Jours",
    "Date Format": "Format de date",
    "12 Hour": "12 heures",
    "24 Hour": "24 heures",
    "Calendar Settings": "Paramètres du calendrier",
    "Calendar Language": "Langue du calendrier",
    "Calendar Date": "Date du calendrier",
    "Calendar Option:": "Option de calendrier :",
    "Events start from": "Les événements commencent à partir de",
    "every": "chaque",
    "Expiration Date": "Date d'expiration",
    "Expiration date": "Date d'expiration",
    
    # ==================== ACTIONS COMMUNES ====================
    "Add": "Ajouter",
    "Add New": "Ajouter nouveau",
    "Edit": "Modifier",
    "Update": "Mettre à jour",
    "Delete": "Supprimer",
    "Delete Permanently": "Supprimer définitivement",
    "Remove": "Retirer",
    "View": "Voir",
    "Save": "Enregistrer",
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
    "Duplicate this item": "Dupliquer cet élément",
    
    # ==================== FORMULAIRES ====================
    "Name": "Nom",
    " Name": " Nom",
    "Title": "Titre",
    "Description": "Description",
    "Content": "Contenu",
    "Category": "Catégorie",
    "Categories": "Catégories",
    "Location": "Lieu",
    "Locations": "Lieux",
    "All Locations": "Tous les lieux",
    "Add New Location": "Ajouter un nouveau lieu",
    "Edit Location": "Modifier le lieu",
    "Venue": "Lieu de l'événement",
    "Venues": "Lieux d'événements",
    "All Venues": "Tous les lieux",
    "Add new venue": "Ajouter un nouveau lieu",
    "Edit venue": "Modifier le lieu",
    "Filter venues": "Filtrer les lieux",
    "Enable": "Activer",
    "Disable": "Désactiver",
    "Enabled": "Activé",
    "Disabled": "Désactivé",
    "Required": "Obligatoire",
    "Optional": "Optionnel",
    "Default": "Par défaut",
    "Default value": "Valeur par défaut",
    "Class": "Classe",
    "Enter Name": "Saisir le nom",
    "Enter title here": "Saisir le titre ici",
    "Enter name ...": "Saisir le nom ...",
    "Enter name customer or some characters in QR Code": "Saisir le nom du client ou quelques caractères du QR Code",
    "Enter number of records in a file": "Saisir le nombre d'enregistrements dans un fichier",
    
    # ==================== NAVIGATION ====================
    "All": "Tous",
    "Next": "Suivant",
    "Previous": "Précédent",
    "First": "Premier",
    "Last": "Dernier",
    "First page": "Première page",
    "Current page": "Page actuelle",
    "Ascending": "Croissant",
    "Descending": "Décroissant",
    "Filter List": "Liste de filtres",
    
    # ==================== CHECK-IN ====================
    "Check-in": "Enregistrement",
    "Check In": "Enregistrer",
    "Checked In": "Enregistré",
    "Check in": "Enregistrement",
    "Checked": "Vérifié",
    "Check-in at ": "Enregistrement à ",
    "Checkin time": "Heure d'enregistrement",
    "Checkin-Time": "Heure d'enregistrement",
    "Check Ticket": "Vérifier le billet",
    "Check ticket": "Vérifier le billet",
    "Check all ticket": "Vérifier tous les billets",
    "Check": "Vérifier",
    "Already Checked In": "Déjà enregistré",
    "Cancel check in": "Annuler l'enregistrement",
    "Canceled check in successfully": "Enregistrement annulé avec succès",
    "Cancel check-in failed": "Échec de l'annulation de l'enregistrement",
    
    # ==================== MESSAGES SYSTÈME ====================
    "Success": "Succès",
    "Error": "Erreur",
    "Warning": "Avertissement",
    "Info": "Information",
    "Loading": "Chargement",
    "Please wait": "Veuillez patienter",
    "Processing": "Traitement en cours",
    "Booking Sucessfully": "Réservation réussie",
    "Cancel Sucessfully": "Annulation réussie",
    "Error Cancellation": "Erreur d'annulation",
    "Creating ticket failed": "Échec de la création du billet",
    "Downloading tickets failed": "Échec du téléchargement des billets",
    "An error occurred while booking tickets.": "Une erreur s'est produite lors de la réservation des billets.",
    "An error occurred while sending email.": "Une erreur s'est produite lors de l'envoi de l'e-mail.",
    "Error: ": "Erreur : ",
    "Exception Error": "Erreur d'exception",
    "Error Something": "Quelque chose ne va pas",
    
    # ==================== PARAMÈTRES ====================
    "Settings": "Paramètres",
    "Event List Settings": "Paramètres de la liste d'événements",
    "General": "Général",
    "General Settings": "Paramètres généraux",
    "Advanced": "Avancé",
    "Advanced Search": "Recherche avancée",
    "Options": "Options",
    "Configuration": "Configuration",
    "Additional Options": "Options supplémentaires",
    "Archive Event Settings": "Paramètres d'archivage des événements",
    "Currency options": "Options de devise",
    "Currency": "Devise",
    "Currency Position": "Position de la devise",
    "Decimal Separator": "Séparateur décimal",
    "Thousand Separator": "Séparateur de milliers",
    "Choosing currency in your country": "Choisir la devise de votre pays",
    "Control the position of the currency symbol": "Contrôler la position du symbole de la devise",
    
    # ==================== PACKAGES ====================
    "Package": "Forfait",
    "Packages": "Forfaits",
    "All Packages": "Tous les forfaits",
    "Add new package": "Ajouter un nouveau forfait",
    "Edit package": "Modifier le forfait",
    "Filter package": "Filtrer les forfaits",
    "Default Package": "Forfait par défaut",
    "Enable Package": "Activer les forfaits",
    "Choose Package": "Choisir un forfait",
    
    # ==================== PAYOUT ====================
    "Payout": "Paiement",
    "All Payout": "Tous les paiements",
    "Add new payout": "Ajouter un nouveau paiement",
    "Edit payout": "Modifier le paiement",
    "Filter payout": "Filtrer les paiements",
    "Payout Method": "Mode de paiement",
    "All Payout Method": "Tous les modes de paiement",
    "Add new payout methodt": "Ajouter un nouveau mode de paiement",
    "Edit payout methodt": "Modifier le mode de paiement",
    "Filter payout methodt": "Filtrer les modes de paiement",
    
    # ==================== MEMBERSHIP ====================
    "Membership": "Adhésion",
    "Memberships": "Adhésions",
    "Add new membership": "Ajouter une nouvelle adhésion",
    "Edit membership": "Modifier l'adhésion",
    "Filter membership": "Filtrer les adhésions",
    
    # ==================== VENDOR/ORGANISATEUR ====================
    "Vendor": "Organisateur",
    "author vendor": "organisateur auteur",
    "author": "auteur",
    "Profile": "Profil",
    "Edit Profile": "Modifier le profil",
    "Dashboard": "Tableau de bord",
    "Wallet": "Portefeuille",
    "Contact": "Contact",
    "Contact Vendor": "Contacter l'organisateur",
    
    # ==================== DIVERS ====================
    "Yes": "Oui",
    "No": "Non",
    "None": "Aucun",
    "Empty": "Vide",
    "and": "et",
    "or": "ou",
    "at": "à",
    "by": "par",
    "from": "de",
    "to": "à",
    "From": "De",
    "Administrator": "Administrateur",
    "Color": "Couleur",
    "Color:": "Couleur :",
    "Background": "Arrière-plan",
    "Background Color": "Couleur d'arrière-plan",
    "Border": "Bordure",
    "Border Color": "Couleur de bordure",
    "Border Radius": "Rayon de bordure",
    "Alignment": "Alignement",
    "Center": "Centre",
    "Left": "Gauche",
    "Right": "Droite",
    "Width": "Largeur",
    "Height": "Hauteur",
    "Size": "Taille",
    "Column": "Colonne",
    "Columns": "Colonnes",
    "1 Column": "1 colonne",
    "2 Columns": "2 colonnes",
    "3 Columns": "3 colonnes",
    "2 columns": "2 colonnes",
    "3 columns": "3 colonnes",
    
    # ==================== TAGS ====================
    "Tag": "Étiquette",
    "Tags": "Étiquettes",
    "All Tags": "Toutes les étiquettes",
    "Add New Tag": "Ajouter une nouvelle étiquette",
    "Edit Tag": "Modifier l'étiquette",
    
    # ==================== GALERIE ====================
    "Gallery": "Galerie",
    "Add Gallery": "Ajouter une galerie",
    "Add image": "Ajouter une image",
    "Add image to profile": "Ajouter une image au profil",
    "Add image(s)": "Ajouter une/des image(s)",
    "Add image(s) to gallery": "Ajouter une/des image(s) à la galerie",
    "Choose image": "Choisir une image",
    "Choose Image": "Choisir une image",
    
    # ==================== SERVICES ====================
    "Extra Services": "Services supplémentaires",
    "Add Service": "Ajouter un service",
    
    # ==================== COUPONS ====================
    "Coupon": "Coupon",
    "Add Coupon": "Ajouter un coupon",
    "Coupon error": "Erreur de coupon",
    
    # ==================== SOCIAL ====================
    "Add Social": "Ajouter un réseau social",
    "Facebook": "Facebook",
    
    # ==================== SEAT/SIÈGE ====================
    "Seat": "Siège",
    "Add Seat": "Ajouter un siège",
    "Add Seat:": "Ajouter un siège :",
    "Add new seat": "Ajouter un nouveau siège",
    "Add person type": "Ajouter un type de personne",
    "Add Area:": "Ajouter une zone :",
    "Add new area": "Ajouter une nouvelle zone",
    "Area": "Zone",
    "Area:": "Zone :",
    "Seat:": "Siège :",
    "Code": "Code",
    "code": "code",
    
    # ==================== BOOKING FORM ====================
    "Choose seat": "Choisir un siège",
    "Choose a date to booking event": "Choisir une date pour réserver l'événement",
    "Choose a mode": "Choisir un mode",
    "Choose type": "Choisir un type",
    "Choose File": "Choisir un fichier",
    "Do you want to insert multiple customer information?": "Voulez-vous saisir plusieurs informations client ?",
    "Create an account to manage booking": "Créer un compte pour gérer les réservations",
    "Confirm Email": "Confirmer l'e-mail",
    "Confirm Password": "Confirmer le mot de passe",
    "Password": "Mot de passe",
    
    # ==================== ADVANCED ====================
    "Checkbox": "Case à cocher",
    "File": "Fichier",
    "Formats: .jpg, .jpeg, .png, .pdf, .doc": "Formats : .jpg, .jpeg, .png, .pdf, .doc",
    "Custom Checkout Field": "Champ de commande personnalisé",
    "Custom Checkout Fields": "Champs de commande personnalisés",
    "Add field": "Ajouter un champ",
    "Basic": "De base",
    "Basic Settings": "Paramètres de base",
    "Basic Infomation": "Informations de base",
    
    # ==================== EMAIL ====================
    "Email Content": "Contenu de l'e-mail",
    "Email address *": "Adresse e-mail *",
    "Email *": "E-mail *",
    "Send": "Envoyer",
    "Recipient": "Destinataire",
    "Subject": "Objet",
    "Allow send email when new user register": "Autoriser l'envoi d'e-mail lors de l'inscription d'un nouvel utilisateur",
    "Allow send mail to each customer (multiple customer)": "Autoriser l'envoi d'e-mail à chaque client (plusieurs clients)",
    "Allow to send an email after a customer books an event successfully": "Autoriser l'envoi d'un e-mail après qu'un client ait réservé un événement avec succès",
    "Allows send email when a vendor requests a withdrawal": "Autoriser l'envoi d'e-mail lorsqu'un organisateur demande un retrait",
    "Allows send email when admin update withdrawal status as completed": "Autoriser l'envoi d'e-mail lorsque l'administrateur met à jour le statut de retrait comme terminé",
    "Allows send email when admin update withdrawal status as canceled": "Autoriser l'envoi d'e-mail lorsque l'administrateur met à jour le statut de retrait comme annulé",
    
    # ==================== WORDPRESS ADMIN ====================
    "Search events": "Rechercher des événements",
    "Find Ticket": "Trouver un billet",
    "Featured": "En vedette",
    "Featured:": "En vedette :",
    "Online": "En ligne",
    "Offline": "Hors ligne",
    
    # ==================== MISC STRINGS ====================
    "%s does not exists": "%s n'existe pas",
    "%s is out of stock": "%s est en rupture de stock",
    "%s Result Found": "%s résultat trouvé",
    "%s Results Found": "%s résultats trouvés",
    "%s item": "%s article",
    "%s items": "%s articles",
    " minutes to complete your payment": " minutes pour compléter votre paiement",
    "Click here to reload the page or the page will automatically reload after 10 seconds.": "Cliquez ici pour recharger la page ou la page se rechargera automatiquement après 10 secondes.",
    "Click here to reload the page or the page will automatically reload after 5 seconds.": "Cliquez ici pour recharger la page ou la page se rechargera automatiquement après 5 secondes.",
    "Click here": "Cliquez ici",
    "Bookmark": "Favori",
    "add to wishlist": "ajouter aux favoris",
    "Wishlist": "Liste de favoris",
    
    # Numbers and symbols (keep as is but translate context)
    "-": "-",
    ": ": " : ",
    "0": "0",
    "1": "1",
    "2": "2",
    "3": "3",
    "4": "4",
    "5": "5",
    "10": "10",
    "50km": "50 km",
    "$": "$",
    "#": "#",
    "A-Z": "A-Z",
    
    # ==================== CONTEXTUAL ====================
    "'s Listing ": " Liste de ",
    "---Select page---": "---Sélectionner une page---",
    "--- Select page ---": "--- Sélectionner une page ---",
    "--- Select Taxonomy ---": "--- Sélectionner une taxonomie ---",
}

def read_pot_file(filepath):
    """Lit le fichier POT et retourne le contenu"""
    with open(filepath, 'r', encoding='utf-8') as f:
        return f.read()

def create_french_po(pot_content, translations):
    """Crée le fichier PO français avec traductions"""
    
    # Header français
    header = f'''# Traduction française pour EventList Plugin
# Copyright (C) 2025 EventList
# Ce fichier est distribué sous la même licence que le paquet EventList.
# Traduction automatique professionnelle, 2025.
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
"X-Generator: Script Python Professionnel\\n"
"X-Domain: eventlist\\n"

'''
    
    lines = pot_content.split('\n')
    output = []
    i = 0
    translated_count = 0
    total_count = 0
    
    # Skip original header
    while i < len(lines):
        if lines[i].startswith('#:') or (lines[i].startswith('msgid') and 'msgid ""' not in lines[i]):
            break
        i += 1
    
    # Process entries
    while i < len(lines):
        line = lines[i]
        
        # Copy comments
        if line.startswith('#'):
            output.append(line)
            i += 1
            continue
        
        # Process msgid
        if line.startswith('msgid "'):
            output.append(line)
            
            # Extract text
            match = re.search(r'msgid "(.*)"', line)
            if match:
                text = match.group(1)
                i += 1
                
                # Handle msgid_plural
                if i < len(lines) and lines[i].startswith('msgid_plural'):
                    output.append(lines[i])
                    i += 1
                    
                    # Translate msgstr[0]
                    if i < len(lines) and lines[i].startswith('msgstr[0]'):
                        trans = translations.get(text)
                        if trans:
                            output.append(f'msgstr[0] "{trans}"')
                            translated_count += 1
                        else:
                            output.append('msgstr[0] ""')
                        i += 1
                    
                    # Translate msgstr[1]
                    if i < len(lines) and lines[i].startswith('msgstr[1]'):
                        trans = translations.get(text)
                        if trans:
                            # Add plural 's' if needed
                            if trans.endswith('e'):
                                plural = trans + 's'
                            elif trans.endswith('u'):
                                plural = trans + 's'
                            elif trans.endswith('s'):
                                plural = trans
                            else:
                                plural = trans + 's'
                            output.append(f'msgstr[1] "{plural}"')
                        else:
                            output.append('msgstr[1] ""')
                        i += 1
                    total_count += 1
                    
                else:
                    # Regular msgstr
                    if i < len(lines) and lines[i].startswith('msgstr'):
                        trans = translations.get(text)
                        if trans:
                            output.append(f'msgstr "{trans}"')
                            translated_count += 1
                        else:
                            output.append('msgstr ""')
                        i += 1
                        
                        # Skip continuation lines
                        while i < len(lines) and lines[i].startswith('"'):
                            i += 1
                        total_count += 1
            else:
                i += 1
        else:
            output.append(line)
            i += 1
    
    return header + '\n'.join(output), translated_count, total_count

# Main execution
pot_file = "eventlist.pot"
po_file = "eventlist-fr_FR.po"

print("=" * 70)
print("TRADUCTION FRANÇAISE COMPLÈTE - EVENTLIST PLUGIN")
print("=" * 70)
print(f"\nFichier source : {pot_file}")
print(f"Fichier destination : {po_file}")
print(f"\nDictionnaire de traduction : {len(TRANSLATIONS)} entrées")

pot_content = read_pot_file(pot_file)
po_content, translated, total = create_french_po(pot_content, TRANSLATIONS)

with open(po_file, 'w', encoding='utf-8') as f:
    f.write(po_content)

print(f"\n{'✓' * 35}")
print(f"✓ Fichier créé avec succès !")
print(f"✓ {translated} chaînes traduites sur {total}")
print(f"✓ Taux de traduction : {(translated/total*100):.1f}%")
print(f"{'✓' * 35}\n")

# Afficher 20 traductions importantes
print("Les 20 traductions les plus importantes :")
print("-" * 70)
priority = [
    "Book Now",
    "Download Ticket",
    "The Cart is empty",
    "Checkout",
    "Booking ID",
    "field is required",
    "Email address is not valid.",
    "Event",
    "Events",
    "Ticket",
    "Tickets",
    "Booking",
    "Customer",
    "Payment",
    "Total",
    "Date",
    "Cancel Booking",
    "Completed",
    "Pending",
    "Check-in",
]

for i, key in enumerate(priority, 1):
    if key in TRANSLATIONS:
        print(f"{i:2}. {key:30} → {TRANSLATIONS[key]}")

print("-" * 70)
print(f"\n✓ Traduction terminée avec succès !")
print(f"✓ Fichier : {po_file}")
print("=" * 70)
