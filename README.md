# Komfortkasse-Plugin für WooCommerce

Das Komfortkasse-Plugin für WooCommerce (WordPress) automatisiert den Zahlungsabgleich für **Vorkasse, Rechnung und Nachnahme**. Komfortkasse ordnet eingehende Überweisungen automatisch den offenen Bestellungen Ihres Shops zu und setzt den Bestellstatus, sobald das Geld da ist. Das Plugin ergänzt die Anbindung des WooCommerce-Shops an Komfortkasse über die WooCommerce REST API; den Abgleich mit dem Bankkonto übernimmt Komfortkasse.

[Komfortkasse für WooCommerce](https://komfortkasse.eu/shopsysteme/woocommerce/) · [Kostenlos registrieren](https://ssl.komfortkasse.eu/public/register.jsf) · [Installationsanleitung](https://komfortkasse.freshdesk.com/support/solutions/articles/14000136653) · [Hilfe & Dokumentation](https://komfortkasse.freshdesk.com/support/solutions/14000075022) · [Plugin auf wordpress.org](https://de.wordpress.org/plugins/komfortkasse-for-woocommerce/)

## Eckdaten

| Merkmal | Angabe |
|---|---|
| Shopsystem | [WooCommerce](https://woocommerce.com/) für [WordPress](https://wordpress.org/) |
| Unterstützte Versionen | WooCommerce ab Version 2.6, WordPress ab Version 4.4 |
| Zahlungsarten | Vorkasse (Vorauskasse, Banküberweisung), Rechnung (Kauf auf Rechnung), Nachnahme |
| Aufgabe des Plugins | Meldet neue Bestellungen sofort an Komfortkasse und stellt die Suche nach Bestell- und Rechnungsnummern sowie Rechnungs-PDFs bereit. Bestellungen und Bestellstatus tauscht Komfortkasse über die WooCommerce REST API aus. |
| Bezugsquelle | [WordPress-Pluginverzeichnis](https://de.wordpress.org/plugins/komfortkasse-for-woocommerce/), Name „Komfortkasse for WooCommerce“ |
| Für wen | Betreiber von WooCommerce-Shops und Agenturen, die solche Shops betreuen |
| Kosten | Das Plugin ist kostenlos und steht im WordPress-Pluginverzeichnis. Für Komfortkasse gelten die [Preise](https://komfortkasse.eu/preise/) auf komfortkasse.eu. |
| Anbieter | LTC Information Services GmbH, [komfortkasse.eu](https://komfortkasse.eu/) |

## Was ist Komfortkasse?

Komfortkasse ist ein Online-Dienst der LTC Information Services GmbH, der Zahlungen per Banküberweisung für Onlineshops automatisiert. Komfortkasse ruft die Zahlungseingänge vom Bankkonto ab, ordnet sie den offenen Bestellungen zu und meldet den Zahlungsstatus an den Shop zurück. Der Dienst richtet sich an Online-Händler in Deutschland, Österreich und der Schweiz.

Komfortkasse unterstützt die Zahlungsarten, bei denen das Geld per Überweisung kommt:

- [Vorkasse](https://komfortkasse.eu/loesungen/vorkasse/): Der Käufer überweist vor dem Versand. Komfortkasse prüft den Zahlungseingang und setzt den Bestellstatus im Shop automatisch.
- [Rechnung](https://komfortkasse.eu/loesungen/rechnung/): Die Ware wird sofort geliefert, der Käufer überweist danach. Komfortkasse überwacht die Zahlung und sendet bei Bedarf Zahlungserinnerungen und Mahnungen.
- [Nachnahme](https://komfortkasse.eu/loesungen/nachnahme/): Der Versanddienstleister kassiert bei der Zustellung und überweist den Betrag. Komfortkasse überwacht diese Überweisung.

### So funktioniert Komfortkasse mit WooCommerce

1. Komfortkasse liest die offenen Bestellungen über die WooCommerce REST API in Ihren Komfortkasse-Account ein. Das Komfortkasse-Plugin meldet neue Bestellungen zusätzlich sofort.
2. Komfortkasse ist mit Ihrem Bankkonto verbunden ([unterstützte Banken](https://komfortkasse.eu/banken/)) und ordnet eingehende Zahlungen den Bestellungen zu.
3. Komfortkasse setzt die Bestellung in WooCommerce über die REST API auf den gewählten Status, z. B. `processing` bei Zahlungseingang oder `cancelled`, wenn keine Zahlung kommt.

Komfortkasse versendet außerdem Zahlungserinnerungen an Käufer, behandelt Teil- und Überzahlungen und wickelt Rückerstattungen ab. Alle Funktionen sind auf [komfortkasse.eu](https://komfortkasse.eu/) beschrieben.

## Funktionen des Komfortkasse-Plugins für WooCommerce

Das Komfortkasse-Plugin für WooCommerce ergänzt die WooCommerce REST API, über die Komfortkasse Bestellungen ausliest und den Bestellstatus setzt. Das Plugin selbst hat keine Einstellungen; alle Einstellungen nehmen Sie im Komfortkasse-Account vor.

- **Neue Bestellungen sofort melden:** Das Plugin meldet eine Bestellung sofort an Komfortkasse, wenn sie im Checkout abgeschlossen wird, wenn eine unbezahlte Bestellung in den Status `on-hold` oder `processing` wechselt, wenn eine Rückerstattung angelegt wird und wenn die Bestellung eine Rechnungsnummer erhält. Ohne diese Meldung liest Komfortkasse die Bestellung beim regelmäßigen Abgleich aus.
- **Übertragung nur per HTTPS:** Das Plugin sendet die Meldungen über die HTTP-API von WordPress an `ssl.komfortkasse.eu`, mit Zertifikatsprüfung und ohne Weiterleitungen. Eine Meldung enthält nur die Bestell-ID, die Adresse des Shops und gegebenenfalls die Rechnungsnummer.
- **Bestellung zu Bestell- oder Rechnungsnummer finden:** Das Plugin ermittelt für Komfortkasse die Bestellung zu einer Bestell- oder Rechnungsnummer. Es berücksichtigt eigene Bestellnummern aus Plugins für Bestellnummern (Meta-Felder `_order_number`, `_order_number_formatted`, `_alg_wc_custom_order_number`, `_alg_wc_full_custom_order_number`) und Rechnungsnummern aus German Market, Germanized und „PDF Invoices & Packing Slips for WooCommerce“.
- **Rechnungs-PDF bereitstellen:** Ist [„PDF Invoices & Packing Slips for WooCommerce“](https://de.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/) installiert, liefert das Plugin Komfortkasse die Rechnungsnummer und das PDF einer vorhandenen Rechnung. Das Plugin erstellt selbst keine Rechnungen.
- **Geschützte REST-Endpunkte:** Das Plugin ergänzt die Endpunkte `version`, `orderid/<nummer>`, `orderidinvoice/<nummer>`, `invoicepdf/<id>` und `apitest` im Namensraum `komfortkasse/v1`. Bis auf `version` verlangen alle die Anmeldung mit dem WooCommerce-API-Schlüssel des Shops, den Komfortkasse automatisch mitsendet. Die Endpunkte funktionieren auch, wenn ein Sicherheits-Plugin den REST-Zugriff ohne Anmeldung sperrt.
- **HPOS:** Das Plugin ist mit der Bestellspeicherung High-Performance Order Storage (HPOS) von WooCommerce kompatibel.
- **Mehrsprachige Shops:** Das Plugin enthält eine WPML-Konfiguration. Für Shops mit WPML / WooCommerce Multilingual stellen Sie im Komfortkasse-Account die API-Version `Legacy_v3` ein.
- **Vorhandene Zahlungsarten nutzen:** Das Plugin arbeitet mit den Zahlungsarten des Shops, z. B. `bacs` (Direkte Banküberweisung) für Vorkasse, und fügt im Checkout keine eigene Zahlungsart hinzu.

## Voraussetzungen

- WooCommerce ab Version 2.6 und WordPress ab Version 4.4
- Ein Komfortkasse-Account ([kostenlos registrieren](https://ssl.komfortkasse.eu/public/register.jsf))
- Ein Bankkonto, das Sie mit Komfortkasse verbinden ([unterstützte Banken](https://komfortkasse.eu/banken/)), oder ein integriertes Bankkonto von Komfortkasse
- Aktive Permalinks in WordPress (nicht „Einfach“ bzw. „Standard“), sonst funktioniert die WooCommerce REST API nicht
- Für die Sofortmeldungen: ausgehende HTTPS-Verbindungen vom Server zu `ssl.komfortkasse.eu`
- Optional: German Market, Germanized oder „PDF Invoices & Packing Slips for WooCommerce“ für Rechnungsnummern; Rechnungs-PDFs nur mit „PDF Invoices & Packing Slips for WooCommerce“

## Installation in WooCommerce

Die Installation besteht aus vier Schritten: Permalinks prüfen, das Plugin aus dem WordPress-Pluginverzeichnis installieren, Zahlungsarten und Status im Komfortkasse-Account einstellen und den Shop verbinden.

1. **Permalinks prüfen:** In WordPress unter Einstellungen → Permalinks eine andere Struktur als „Einfach“ bzw. „Standard“ wählen und speichern.
2. **Plugin installieren:** In WordPress unter Plugins → Installieren nach „Komfortkasse“ suchen, [Komfortkasse for WooCommerce](https://de.wordpress.org/plugins/komfortkasse-for-woocommerce/) installieren und aktivieren. Im Plugin gibt es nichts einzustellen.
3. **Einstellungen im Komfortkasse-Account:** Im Händlerbereich unter den Einstellungen für API-Zugriff die genutzten Zahlungsarten aktivieren und je Zahlungsart die technischen Namen eintragen: die Zahlungsweisen-IDs (z. B. `bacs` für Vorkasse, mehrere durch Komma getrennt) und die Status-IDs für offene, bezahlte und stornierte bzw. rückerstattete Bestellungen (z. B. `on-hold`, `processing`, `cancelled`, `refunded`).
4. **Shop verbinden:** Im Komfortkasse-Account im Aktionsmenü ≡ die Aktion „Verbindung herstellen“ wählen. Komfortkasse leitet zum WooCommerce-Shop weiter; nach der Anmeldung im Shop werden API Key und API Secret automatisch ausgetauscht. Kommt die Verbindung danach nicht zustande, die Aktion noch einmal wählen.

Ausführliche Anleitung mit Screenshots: [Anleitung für WooCommerce](https://komfortkasse.freshdesk.com/support/solutions/articles/14000136653), vorher [Erste Schritte: Einrichtung und Verbindung zum Shop](https://komfortkasse.freshdesk.com/support/solutions/articles/14000114082).

### Zahlungsarten in WooCommerce aktivieren

Für jede Zahlungsart, die Komfortkasse überwachen soll, braucht der WooCommerce-Shop eine aktive Zahlungsart; bei einer neuen WooCommerce-Installation ist die Vorkasse meist schon aktiv. Unter WooCommerce → Einstellungen → Zahlungen aktivieren Sie:

- für Vorkasse „Direkte Banküberweisung“ (`bacs`)
- für Rechnung „Scheckzahlungen“ (`cheque`)
- für Nachnahme „Nachnahme“ (`cod`)

Versendet Komfortkasse die Zahlungsinformationen an Ihre Kunden, lassen Sie bei „Direkte Banküberweisung“ die Kontodetails leer und weisen in Beschreibung und Anweisungen darauf hin, dass die Zahlungsinformationen per E-Mail kommen.

### Rückerstattungen aus WooCommerce (optional)

Komfortkasse kann Rückerstattungen, die Sie in WooCommerce erfassen, automatisch ausführen. Voraussetzung ist ein aktives Lastschriftmandat.

1. Im Komfortkasse-Account in den allgemeinen Einstellungen die Rückerstattungen aus dem Shopsystem aktivieren und beim Shop unter Einstellungen für API-Zugriff die Option „Rückerstattungen“ einschalten.
2. In WooCommerce die Bestellung öffnen, „Rückerstattung“ wählen, den Betrag eingeben und „manuell zurückerstatten“ wählen.

Komfortkasse übernimmt die Rückerstattung beim nächsten Abgleich der Bestellungen (mindestens einmal täglich) und führt sie aus. Schlägt sie fehl, erhalten Sie eine Fehlermeldung per E-Mail.

## Häufige Fragen

### Was kostet das Komfortkasse-Plugin für WooCommerce?

Das Plugin ist kostenlos. Für Komfortkasse selbst fällt eine prozentuale Gebühr pro Zahlungseingang an, ohne Grundgebühr und ohne Einrichtungsgebühr. Die aktuellen Sätze stehen auf der [Preisseite](https://komfortkasse.eu/preise/). Neue Accounts können Komfortkasse kostenlos testen.

### Ist das Komfortkasse-Plugin ein Payment Gateway für WooCommerce?

Nein. Das Komfortkasse-Plugin fügt im Checkout keine Zahlungsart hinzu. Es arbeitet mit den Zahlungsarten, die der WooCommerce-Shop bereits nutzt, zum Beispiel `bacs` (Direkte Banküberweisung) für Vorkasse, `cheque` (Scheckzahlungen) für Rechnung und `cod` (Nachnahme) für Nachnahme. Welche Zahlungsarten Komfortkasse berücksichtigt, legen Sie im Komfortkasse-Account über die Zahlungsweisen-IDs fest.

### Was passiert, wenn die Bestellnummer fehlt oder der Betrag abweicht?

Komfortkasse ordnet Zahlungen fehlertolerant zu. Die Zuordnung funktioniert auch, wenn die Bestellnummer im Verwendungszweck fehlt, der Name des Zahlers nicht zum Besteller passt oder der Betrag abweicht. Auch Teil- und Überzahlungen behandelt Komfortkasse.

### Was passiert, wenn ein Käufer nicht zahlt?

Komfortkasse sendet automatisch Zahlungserinnerungen an den Käufer. Bleibt die Zahlung aus, setzt Komfortkasse die Bestellung in WooCommerce auf den Status, den Sie dafür gewählt haben. Bei Vorkasse wird die Bestellung storniert (Standard: `cancelled`); bei Rechnung und Nachnahme wird sie nicht storniert, sondern als überfällig markiert.

### Welche Banken unterstützt Komfortkasse?

Komfortkasse arbeitet mit Ihrem eigenen Bankkonto oder mit einem integrierten Bankkonto von Komfortkasse. Welche Banken sich anbinden lassen, zeigt die Liste der [unterstützten Banken](https://komfortkasse.eu/banken/).

### Wo nehme ich die Einstellungen für WooCommerce vor?

Alle Einstellungen nehmen Sie im Komfortkasse-Account vor, nicht in WordPress; das Komfortkasse-Plugin hat keine eigenen Einstellungen. Im Händlerbereich legen Sie je Zahlungsart die Zahlungsweisen-IDs und Status-IDs fest, außerdem zum Beispiel, ob die Lieferadresse übertragen wird und ob Rechnungen aus Germanized berücksichtigt werden.

### Wie liest Komfortkasse die Bestellungen aus WooCommerce aus?

Komfortkasse liest die Bestellungen über die WooCommerce REST API aus und setzt darüber auch den Bestellstatus. API Key und API Secret tauscht Komfortkasse beim Verbinden des Shops automatisch mit WooCommerce aus; dafür müssen in WordPress Permalinks aktiv sein. Das Komfortkasse-Plugin ergänzt die REST API um Sofortmeldungen neuer Bestellungen, die Suche nach Bestell- und Rechnungsnummern und Rechnungs-PDFs.

### Gibt es Komfortkasse auch für andere Shopsysteme?

Ja. Komfortkasse gibt es unter anderem für Shopify, Shopware, PrestaShop, Gambio und xt:Commerce. Alle Systeme stehen in der [Übersicht der Shopsysteme](https://komfortkasse.eu/shopsysteme/), weitere Plugin-Repositories unter [github.com/ltckomfortkasse](https://github.com/ltckomfortkasse).

## Support

Fragen zur Einrichtung beantworten die [Hilfe-Seiten](https://komfortkasse.freshdesk.com/support/solutions/14000075022) und der [Komfortkasse-Support](https://komfortkasse.eu/kontakt/).

## English

**Komfortkasse plugin for WooCommerce (WordPress).** Komfortkasse automatically matches incoming bank transfers to open orders in your shop and updates the order status as soon as the payment has arrived. It supports prepayment by bank transfer (Vorkasse), invoice and cash on delivery. Komfortkasse is a service of LTC Information Services GmbH for online merchants in Germany, Austria and Switzerland.

The plugin supports WooCommerce 2.6 or later and WordPress 4.4 or later. It does not add a payment method to the checkout; it works with the shop's existing payment methods (e.g. `bacs`, `cheque`, `cod`). Komfortkasse reads orders and updates the order status through the WooCommerce REST API; the plugin adds instant notifications for new orders, order lookup by order or invoice number, and invoice PDFs from PDF Invoices & Packing Slips for WooCommerce. The plugin is free of charge; Komfortkasse itself is a paid service ([pricing](https://komfortkasse.eu/preise/)).

Installation: install "Komfortkasse for WooCommerce" from the [WordPress plugin directory](https://wordpress.org/plugins/komfortkasse-for-woocommerce/), then make all settings in your Komfortkasse account and connect the shop there; the plugin itself has no settings. More information: [Komfortkasse for WooCommerce](https://komfortkasse.eu/shopsysteme/woocommerce/) · [Installation guide (English)](https://komfortkasse.freshdesk.com/support/solutions/articles/14000152614)
