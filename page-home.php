<?php get_header(); ?>

<main class="site-main">
<section class="intro container">
  <p>
    <span class="pull-quote">A simple concept, quality assured by our experienced expert area principals.</span>
The Assessment Hub brings the essential medico and allied health specialists you require into the One Location.
Same day assessment by multiple medico and allied health experts avoids extended lag times between one expert’s assessment and report and the next.
You receive a more current, reliable, expert report. Expedite your decision making processes.
  </p>
</section>

<section class="how-it-works container">
  <div class="grid">

    <div class="col-12">
            <h2 class="center"><span class="underline">How it Works?</span></h2>
    </div>

    <div class="col-3_md-6_sm-12">
      <h3>Choose Your Assesments</h3>
      <img class="icon" src="<?php echo get_template_directory_uri(); ?>/imgs/icons/choose.svg">
    </div>
    <div class="col-3_md-6_sm-12">
      <h3>Tell us the purpose of the assessments.</h3>
        <img class="icon" src="<?php echo get_template_directory_uri(); ?>/imgs/icons/question.svg">
    </div>
    <div class="col-3_md-6_sm-12">
      <h3>Claimant sees more than one expert on the same day</h3>
        <img class="icon" src="<?php echo get_template_directory_uri(); ?>/imgs/icons/experts.svg">
    </div>
    <div class="col-3_md-6_sm-12">
      <h3>You receive the right reports, on time</h3>
        <img class="icon" src="<?php echo get_template_directory_uri(); ?>/imgs/icons/purpose.svg">
    </div>
    </div>
</section>

<section class="team full-w green-bg">
  <div class="container">
    <h3 class="underline">Our Experts</h3>
    <ul class="team">
      <li>
        Occupational Physicians 
      </li>
      <li>
        Psychologists
      </li>
      <li>
        Rehab Counsellors
      </li>
      <li>
        Exercise Physiologists
      </li>
      <li>
        Physiotherapists
      </li>
      <li>
        Occupational Therapists
      </li>
    </ul>
    <h3 class="underline">Join Our Team</h3>
    <p>We are always looking for industry experts to join our ever growing team, if you or somebody that you know is interested, please fill out the form below and a staff member will be in touch shortly</p>
    <form class="white-form join-form" method="POST" id="join" join>
      <input type="text" name="name" placeholder="Name">
      <input type="text" name="email" placeholder="Email">
      <input type="text" name="phone" placeholder="Phone Number">
      <input type="submit" class="btn btn--inverse center" value="Enquire">
    </form>
    <div id="join-message"></div>
  </div>
</section>
</main>
    </div>
  <!-- </div> -->
<?php get_footer(); ?>
