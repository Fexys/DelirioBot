<?

global $bio, $bio_tot;

$bio['Mte90']['bio']="Fan sfegatato di Debian e KDE. Superpotere: Sente la puzza di Gnome da lontano un chilometro.";
$bio['Mte90']['insulto'][]="Ti predo a calci col piedone puzzoso";
$bio['Mte90']['insulto'][]="Sempre a litigare con questa tastiera e usa il correttore automatico";

$bio['v2']['bio']="Nome in codice: V2. Ricercata e pericolosa Cyberpunker, psicolabile e squilibrata.";
$bio['v2']['insulto'][]="Tagliati sti baffi";

$bio['gianguido']['bio']="I was chatting here, but then i took an arrow to the knee";
$bio['gianguido']['insulto'][]="Sto Android te lo frullo";

$bio['worst']['bio']="Nerd mancato, apprendista smanettone, socialmente impedito. Innamorato di Debian&Openbox, detesta tutto ciò che è imposto.";
$bio['worst']['insulto'][]="Ma ti pinnizzo io <3";

$bio['toketin']['bio']="Arciere convinto, studente universitario a Udine e juventino. Amo la configurabilità e la leggerezza.";
$bio['toketin']['insulto'][]="Tu e sto calcio del minghia";

$bio['quizzlo']['bio']="Fanatico di Gentoo. Toglietemi tutto, ma non la mia riga di comando <3.";
$bio['quizzlo']['insulto'][]="Ma ti gentoizzo io <3";

$bio['blacktux']['bio']="Stupratore seriale di xterm. Adulatore di awesomewm. Masticatore di chip music. Nerd girls lover.";
$bio['blacktux']['insulto'][]="Sei l'anello mancante tra uomo e scimmia";

$bio['ilDelirante']['bio']="Padre Mte90, Madre ignota. Questo ha creato in lui gravi turbe mentali.";
$bio['ilDelirante']['insulto'][]="Non mi scassare le balle";
$bio['ilDelirante']['insulto'][]="Ti piglio a sberle finchè non diventi bello e la vedo lunga";
$bio['ilDelirante']['insulto'][]="Si vede che ti piace il tuo mestiere.. incassi tanto legnate";

$bio['picchio']['bio']="Pseudo nerd, amante delle puppe, fancazzista professionista e Linaro. Creatore del mitico #TetteTime.";
$bio['picchio']['insulto'][]="Diventi cieco di questo passo";
$bio['picchio']['insulto'][]="Ma io ti pacchio e anche forte";
$bio['picchio']['insulto'][]="Sei cosi scemo che se vai al cinema e leggi 'vietato ai minori di 18' torni con 17 amici";

$bio['alessandro1997']['bio']="Appassionato di informatica, programmazione, open source. E scrittura. Un paradosso vivente.";
$bio['alessandro1997']['insulto'][]="sei brutto, ma così brutto che non ti sorridono neanche i sofficini";

$bio['gigitux']['bio']="Sono un Nerd, amo l'informatica ed il mondo open-source.Mi piacciono i videogiochi.";
$bio['gigitux']['insulto'][]="Ti faccio conoscere 25 cm di simpatia se vieni qui";

$bio['PTKDev']['bio']="Non so usare windows. Non mangio le mele. Lascio che i bit scorrano velocemente sul mio debian. Penso troppo, sono pazzo, sono semplicemente io: vaffanculo!";
$bio['PTKDev']['insulto'][]="Ti faccio conoscere 25 cm di simpatia se vieni qui";

$bio['vencizOn']['bio']="Studente di Informatica. Aspirante developer. Innamorato di Google. Pseudo blogger nel tempo libero.";
$bio['vencizOn']['insulto'][]="L'unico tuo neurone soffre cosi tanto di depressione per la solitudine che ha scelto come amico immaginario la goccia di sodio della Lete, l'unica che secondo lui lo può capirequesto è";

$bio['turlando']['bio']="Danghiù veri macc for riid mai bio bat i donnt spik prosciutti";
$bio['turlando']['insulto'][]="Prima di conoscerti ero sull'orlo del precipizio... adesso che ti ho conosciuto credo che farò un passo in avanti";
$bio['turlando']['insulto'][]="Sei simpatico come una famiglia di ricci intu u culu";
$bio['turlando']['insulto'][]="Ti faccio conoscere 25 cm di simpatia se vieni qui";

foreach($bio as $key => $value) {
    $bio_tot[]=$key;
}

/* supporto per i nick uguali ma con _ */
foreach($bio as $key => $value) {
    $bio_[$key."_"]=$value;
}

//Supporto nick diversi casi speciali
$bio['v2_dev']=$bio['v2'];
$bio['picchiopc']=$bio['picchio'];

$bio = array_merge($bio,$bio_);

?>