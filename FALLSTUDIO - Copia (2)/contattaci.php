<?php
$pageTitle = 'Fall Studio | Contattaci';
$extraHead = '
<style>
    .contact-section {
        padding: 5rem 0;
    }

    .contact-form {
        max-width: 600px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .contact-form input,
    .contact-form textarea {
        padding: 1rem;
        border: 1px solid #ccc;
        font-family: \'Inter\', sans-serif;
    }

    .contact-form button {
        padding: 1rem;
        background: #000;
        color: #fff;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.3s;
    }

    .contact-form button:hover {
        background: #333;
    }

    .contact-info {
        text-align: center;
        margin-bottom: 3rem;
    }
</style>';
include 'header.php';
?>

    <section class="contact-section">
        <div class="container">
            <h2 class="section-title">Contattaci</h2>

            <div class="contact-info">
                <p>Hai domande o bisogno di supporto?<br>Scrivici compilando il form qui sotto o invia una email a
                    info@fallstudio.it</p>
            </div>

            <form class="contact-form">
                <input type="text" placeholder="Il tuo nome" required>
                <input type="email" placeholder="La tua email" required>
                <textarea rows="5" placeholder="Il tuo messaggio" required></textarea>
                <button type="submit">Invia Messaggio</button>
            </form>
        </div>
    </section>

<?php
include 'footer.php';
?>
