news library for gino CMS by Otto Srl, MIT license
===================================================================
Release 1.12.1 - Requires gino 1.1

Libreria per la gestione di news categorizzate e non.   
La documentazione per lo sviluppatore della versione 1.11 (generata con doxygen) è contenuta all'interno della directory doc.   
La documentazione dell'ultima versione disponibile la si trova qui:    
http://otto-torino.github.com/gino-news

CARATTERISTICHE
------------------------------
- titolo
- categoria (opzionale)
- data
- testo
- immagine (ridimensionamento e creazione thumb automatizzati)
- file allegato
- condivisione social networks
- visualizzazione ristretta a gruppi di utenti di sistema
- gestione della pubblicazione e di un gruppo di utenti redattori

OPZIONI CONFIGURABILI
------------------------------
- titolo ultime news
- titolo elenco news
- titolo vetrina ultime news
- visualizzazione categorie
- numero di news visualizzate
- numero di caratteri mostrati nei riassunti
- modalità di visualizzazione della news completa (layer, expand, nuova pagina)
- dimensioni layer (vedi opzione precedente)
- effetto lightbox sull'immagine
- modulo di ricerca visibile
- larghezza delle immagini a seguito di ridimensionamento e creazione thumb
- feed RSS

OUTPUTS
------------------------------
- lista ultime news (numero configurabile da opzioni)
- lista completa news paginata
- vetrina ultime news

INSTALLAZIONE
------------------------------
Per installare questa libreria seguire la seguente procedura:

- creare un pacchetto zip di nome "news_pkg.zip" con tutti i file e le cartelle eccetto README.md e la directory doc
- loggarsi nell'area amministrativa e entrare nella sezione "moduli di sistema"
- seguire il link (+) "installa nuovo modulo" e caricare il pacchetto creato al punto 1
- creare nuove istanze del modulo nella sezione "moduli" dell'area amministrativa.
