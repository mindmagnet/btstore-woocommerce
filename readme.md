#Manual de utilizare pentru WooCommerce eBTPay

Modulul de WooCommerce eBTPay funcționează pe modelul Authorize-Capture/Void, deci
la plasarea comenzii suma este autorizată (debitată de pe cardul clientului), iar la livrare ea
este capturată (virată în contul comerciantului); în caz de imposibilitate de livrare, în loc de
capturare comanda este anulată (void) iar banii sunt returnați clientlui. Există și posibilitatea
void-ului după capturare.
##Instalarea Modulului
##Dependențe
Pentru a putea utiliza modului de plată WooCommerce eBTPay, este necesar ca utilizatorul
să aibă instalate următoarele:

• WordPress 3.5 minim

• WooCommerce Extension 2.1.6 minim

• toate dependețele necesare pentru cele două

*Modulul de plată a fost dezvoltat și testat pe versiunea 2.1.6 de WooCommerce și
Wordpress 3.5 – 3.8, există posibilitatea de a funcționa corect și pentru versiuni mai vechi de
WooCommerce sau versiuni mai noi de WordPress, însă utilizarea modului pentru aceste
versiuni se face pe proprie răspundere.
##Instalare
1. Descărcați și instalați platforma WordPress, versiunea 3.5 sau mai nouă. Aceasta se
găsește la: http://wordpress.org/download/
2. Verificați funcționarea corectă a WordPress
3. Descărcați și instalați extensia WooCommerce, versiunea 2.1.6 sau mai nouă.
Aceasta se găsește la: http://www.woothemes.com/woocommerce/
4. Verificați instalarea corectă a WooCommerce
5. Accesați interfața **admin** a WordPress (exemplu.com/wp-admin), apoi mergeți la
meniul **Plugins, Add New, Upload,** selectați fișierul .zip care conține modulul
eBTPay și apăsați pe **Install Now**.
6. Navigați din nou la **Plugins, Installed Plugins** și în lista va apărea modulul eBTPay.
Apăsați **Enable**.
7. După activarea modulului, navigați la meniul **WooCommerce, Settings, Checkout,**
iar acolo veți observa acum două secțiuni noi, **BTPay Standard și BTPay Star**.
8. Accesați oricare dintre cele două și introduceți în zona **API Settings** informațiile de
configurare primite de la bancă. Apoi faceți click pe **Save changes**. Configurațiile
introduse vor fi salvate automat și pentru cealaltă metodă de plată.
9. De asemenea, există anumite setări care sunt specifice fiecărei metode de plată în
parte, vă sfătuim să le configurați după preferințe.
10. În acest punct, modulul ar trebui să fie activ și pregătit de funcționare.

*În cazul în care întâmpinați dificultăți la instalare, vă rugăm să ne contactați pentru suport.

##Autorizarea
La plasarea comenzii, metodele de plată eBTPay sunt afișate clientului iar după plasarea
comenzii acesta este redirectat la pagina de plată RomCard unde va introduce datele de
card; cardurile înrolate vor fi duse la pasul de 3D secure, iar după finalizarea plății,
utilizatorul este întors la site-ul comerciantului.

În panoul de administrare WooCommerce, comanda astfel plasată va avea în secțiunea de
comentarii (order notes) rezultatul autorizării cu mesajul de succes sau eventual cel de
eroare.

##Capturarea
Pentru a captura o sumă, trebuie utilizată interfața BT Pay API Calls din căsuța Transaction
History, unde se selectează la API Call Action opțiunea „Încasare plată”. De asemenea, se
poate seta și o sumă care să fie autorizată, sau se poate lăsa valoarea implicită care
reprezintă totalul comenzii. În plus, dacă se apasă pe butonul „Informații suplimentare” se
pot vizualiza și modifica valuta în care se capturează plata, dar și anumite informații primite
de la portalul de plăți RomCard. După selectarea opțiunii și configurarea detaliilor, se apasă
pe butonul „Trimitere către BT Pay” și apoi utilizatorul este redirecționat către portalul
RomCard, unde se parcurg pașii necesari și apoi se revine la pagina inițială.

Dacă răspunsul primit de la portalul de plată este valid și tranzacția este aprobată, statusul
comenzii se va schimba pe „completed” și se va adăuga la notele comenzii răspunsul primit
de la bancă.

##Anularea
Anularea unei plăți (void) se face într-un mod similar cu capturarea, folosind aceeași
interfață BT Pay API Calls, singura diferență fiind aceea că se selectează „Anulare plată” ca
și API Call Action. Anularea poate fi făcută chiar și după capturarea plății, în acest caz se
face o rambursare, parțială sau completă, către client. În cazul în care plata nu a fost
capturată, statusul comenzii se va schimba pe „cancelled”, iar în cazul în care plata a fost
capturată anterior, se va schimba pe „refunded”.

*Toate tranzacțiile desfășurate între modulul de plată și bancă pot fi consultate în secțiunea
**Transaction History**, împreună cu detaliile aferente.
