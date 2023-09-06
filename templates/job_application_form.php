<?php
/**
 * @zyonstudios
 */
?>
<h4>Apply below for <?php echo esc_attr( get_the_title() ); ?></h4>
<form id="job-application-form" method="post" enctype="multipart/form-data">
    <?php echo '<input type="hidden" name="job_application_nonce" value="' . esc_attr( $nonce ) . '">';?>
    <!-- Add a hidden field for the post title -->
    <input type="hidden" name="post_title" value="<?php echo esc_attr( get_the_title() ); ?>">

    <div>
        <label for="applicant_name">Name:</label>
        <input type="text" name="applicant_name" id="applicant_name" required>
    </div>

    <div>
        <label for="applicant_email">Email:</label>
        <input type="email" name="applicant_email" id="applicant_email" required>
    </div>

    <div>
        <label for="message">Message:</label>
        <textarea style="background-color:#f1f1f1 !important;" name="message" id="message" required></textarea>
    </div>
<br>
    <div>
        <label for="cv_attachment">Attach your cv:</label>
        <input type="file" name="cv_attachment" id="cv_attachment" accept=".pdf,.doc,.docx" required>
    </div>
<br>
    <div>
        <input type="submit" name="submit_job_application" value="Submit Application" style="background-color:green;">
    </div>
    
</form>
