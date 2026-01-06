PHP & MySQL Todo App
Tämä on kevyt ja tietoturvallinen "To-Do" -sovellus, joka on toteutettu PHP-taustajärjestelmällä ja Vanilla JavaScript -käyttöliittymällä. Sovellus mahdollistaa käyttäjäkohtaisten tehtävälistojen hallinnan.

    Ominaisuudet

Käyttäjähallinta: Rekisteröityminen ja kirjautuminen suojatulla istunnonhallinnalla.

Tehtävien hallinta (CRUD): Tehtävien luominen, lukeminen, päivittäminen ja poistaminen.

Lisätiedot: Mahdollisuus lisätä tehtäville vapaavalintainen kuvaus ja tärkeysaste (Low, Medium, High).

Tietoturva: Salasanojen hajautus password_hash-funktiolla sekä SQL-injektioiden esto valmisteltujen kyselyiden (Prepared Statements) avulla.

Responsiivinen käyttöliittymä: Tumma teema, joka on toteutettu nykyaikaisilla CSS-muuttujilla.

    Teknologiat

Frontend: HTML5, CSS3 ja Vanilla JavaScript (AJAX/Fetch API).

Backend: PHP (proseduraalinen tyyli).

Tietokanta: MySQL.

    Tiedostorakenne

index.html & register.html: Sisäänkirjautumis- ja rekisteröitymislomakkeet.

tasks.html: Sovelluksen päänäkymä ja tehtävien hallinnan logiikka.

api.php: Keskitetty päätepiste, joka käsittelee kaikki AJAX-pyynnöt JSON-muodossa.

auth.php: Käyttäjän todennukseen, istuntoihin ja rekisteröintiin liittyvät funktiot.

config.php: Tietokantayhteyden muodostaminen ympäristömuuttujia hyödyntäen.

styles.css: Sovelluksen visuaalinen ilme ja asettelu.

database.sql: Tietokannan skeema (taulut: users ja tasks).

    Asennus (Local Development)

Kloonaa repositorio tai lataa tiedostot XAMPP:n htdocs-kansioon.

Luo MySQL-tietokanta nimeltä todo.

Tuo database.sql tiedoston sisältö tietokantaan.

Varmista, että config.php tiedostossa on oikeat tietokantatunnukset (oletuksena XAMPP:ssa käyttäjä root ilman salasanaa).

Avaa selaimella localhost/projektin-kansio/index.html.

    Julkaisu (Railway.app)
    
Sovellus on optimoitu julkaistavaksi Railway-palveluun:

Yhdistä GitHub-repositorio Railwayhin.

Lisää MySQL-palvelu Railway-projektiin.

Määritä tarvittavat ympäristömuuttujat (MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT) Railwayn hallintapaneelissa, jotta config.php saa yhteyden tietokantaan.