<?php get_header(); ?>
<main class="site-main">
  <section class="container">
    <h2 class="underline">Contact Us</h2>
    <p>
      Use the form below, or the contact details below to contact us. If you need to submit a booking request you can do so <a href="/booking">here.</a> <br />
      <span class="bold">Address: <br /></span>15 Albion St<br />Harris Park<br />NSW 2150<br />
      <span class="bold">Email:</span> <br /><a href="mailto:assessments@assessmenthub.com.au">assessments@assessmenthub.com.au</a>
    </p>

    <form class="grey-form contact-form">
      <div class="grid">
      <div class="col">
        <input type="text" name="firstname" placeholder="Name">
        <input type="text" name="email" placeholder="Email">
        <input type="text" name="number" placeholder="Phone Number">
      </div>
      <div class="col">
        <textarea name="message">Message</textarea>
      </div>
    </div>
      <input type="submit" class="btn btn--inverse center" value="Enquire" placeholder="Message">
    </form>
  </section>

  <section class="team full-w green-bg">
    <div class="container">
      <h3 class="underline">Join Our Team</h3>
      <p>We are always looking for industry experts to join our ever growing team, if you or somebody that you know is interested, please fill out the form below and a staff member will be in touch shortly</p>
      <form class="white-form join-form" method="POST" action="" id="join">
        <input type="text" name="name" placeholder="Name">
        <input type="text" name="email" placeholder="Email">
        <input type="text" name="number" placeholder="Phone Number">
        <input type="submit" class="btn btn--inverse center" value="Enquire">
      </form>
    </div>
  </section>

</main>
<?php get_footer(); ?>
