<?

global $bio, $bio_tot;

$bio['Mte90']['bio']="Fan sfegatato di Debian e KDE. Superpotere: Sente la puzza di Gnome da lontano un chilometro.";

$bio['v2']['bio']="Nome in codice: V2. Ricercata e pericolosa Cyberpunker, psicolabile e squilibrata. ";

$bio['gianguido']['bio']="I was chatting here, but then i took an arrow to the knee";

$bio['worst']['bio']="Nerd mancato, apprendista smanettone, socialmente impedito. Innamorato di Debian&Openbox, detesta tutto ciò che è imposto.";

$bio['toketin']['bio']="Arciere convinto, studente universitario a Udine e juventino. Amo la configurabilità e la leggerezza.";

$bio['quizzlo']['bio']="Fanatico di Gentoo. Toglietemi tutto, ma non la mia riga di comando <3.";

foreach($bio as $key => $value) {
    $bio_tot[]=$key;
}

/* supporto per i nick uguali ma con _ */
foreach($bio as $key => $value) {
    $bio_[$key."_"]=$value;
}

$bio = array_merge($bio,$bio_);

?>