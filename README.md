news library for gino CMS by Otto Srl, MIT license
===================================================================
Release 2.0.2 - Requires gino 1.3.0

Libreria per la gestione di news categorizzate.   
La documentazione per lo sviluppatore della versione 2.0 (generata con doxygen) è contenuta all'interno della directory doc.    
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
- personalizzazione dei template di visualizzazione da opzioni
- contenuti ricercabili attraverso il modulo "Ricerca nel sito" di Gino
- contenuti resi disponibili al modulo newsletter di Gino (il modulo deve essere installato sul sistema)

OPZIONI CONFIGURABILI
------------------------------
- titolo ultime news
- titolo elenco news
- titolo vetrina ultime news
- template singolo elemento nella vista ultime news
- numero di ultime news visualizzate
- template singolo elemento nella vista archivio news
- numero news per pagina
- template singolo elemento nella vista vetrina ultime news
- numero di news mostrate nella vetrina
- animazione automatica elementi vetrina
- intervallo animazione automatica
- template dettaglio news
- larghezza massima immagine
- larghezza thumb

OUTPUTS
------------------------------
- lista ultime news (numero configurabile da opzioni)
- lista completa news paginata
- vetrina ultime news
- feed RSS

INSTALLAZIONE
------------------------------
Per installare questa libreria seguire la seguente procedura:

- creare un pacchetto zip di nome "news_pkg.zip" con tutti i file e le cartelle eccetto README.md e la directory doc
- loggarsi nell'area amministrativa e entrare nella sezione "moduli di sistema"
- seguire il link (+) "installa nuovo modulo" e caricare il pacchetto creato al punto 1
- creare nuove istanze del modulo nella sezione "moduli" dell'area amministrativa.
