Modulo news per gino CMS by Otto Srl, MIT license {#mainpage}
==============================================================
Libreria per la gestione di news categorizzate.
La documentazione per lo sviluppatore è contenuta all'interno della directory doc.
La documentazione dell'ultima versione disponibile (2.1.0) si trova qui:

http://otto-torino.github.io/gino-news/

# CARATTERISTICHE

- data
- categorie
- titolo
- slug (pretty url)
- testo
- tag
- immagine (con ridimensionamento)
- file allegato
- visualizzazione ristretta a gruppi di utenti di sistema
- condivisione social networks
- gestione della pubblicazione e di un gruppo di utenti redattori
- personalizzazione dei template
- contenuti ricercabili attraverso il modulo "Ricerca nel sito" di Gino
- contenuti resi disponibili al modulo newsletter di Gino (il modulo deve essere installato sul sistema)
- feed RSS

# OPZIONI CONFIGURABILI

- numero di ultime news visualizzate
- numero news per pagina in archivio
- numero di news mostrate nella vetrina
- animazione automatica elementi vetrina
- intervallo animazione automatica
- larghezza massima immagine
- numero di news esportabili per la newsletter

# OUTPUTS PER INSERIMENTO IN LAYOUT

- lista ultime news (numero configurabile da opzioni)
- vetrina ultime news

# OUTPUTS

- dettaglio news
- lista completa news paginata
- feed RSS

# INSTALLAZIONE

Per installare questa libreria seguire la seguente procedura:

- creare un pacchetto zip di nome "news_pkg.zip" con tutti i file e le cartelle eccetto README.md, Doxyfile e la directory doc
- loggarsi nell'area amministrativa e entrare nella sezione "moduli di sistema"
- seguire il link (+) "installa nuovo modulo" e caricare il pacchetto creato al punto 1
- creare nuove istanze del modulo nella sezione "moduli" dell'area amministrativa.

# RELEASES

2014/12/10 | v 2.1.0 | Richiede gino 2.0.0  
2016/05/17 | v 2.2.0 | Nuova versione  

# Copyright
Copyright © 2005-2016 [Otto srl](http://www.otto.to.it), [MIT License](http://opensource.org/licenses/MIT)