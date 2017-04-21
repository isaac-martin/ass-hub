<?php get_header(); ?>

<main class="site-main">
<section class="intro container">
  <p>
<?php the_field('intro_text');?>
  </p>
</section>

<section class="how-it-works container">
  <div class="grid">
    <div class="col-12">
          <h2><span class="underline">How it Works?</span></h2>
    </div>

    <?php if( have_rows('how_it_works') ):
    while( have_rows('how_it_works') ): the_row();
    $icon = get_sub_field('icon');
		$content = get_sub_field('text');
    ?>

    <div class="col-3_md-6_sm-12">
      <h3><?php echo $content ?></h3>
      <img class="icon" src="<?php echo $icon ?>">
    </div>

    	<?php endwhile; ?>
      <?php endif; ?>
    </div>
</section>

<section class="team full-w green-bg">
  <div class="container">
    <h3 class="underline">Our Experts</h3>

    <?php if( have_rows('our_experts') ):?>
      <ul class="team">
    <?php while( have_rows('our_experts') ): the_row(); ?>

      <li><?php the_sub_field('expert'); ?></li>

      <?php endwhile; ?>

    </ul>

    <?php endif; ?>

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
