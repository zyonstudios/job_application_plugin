<?php
/*
Plugin Name: Job Application Plugin
Plugin URI:  https://www.zyonstudios.co.uk/
Description: A WordPress plugin for job applications with front-end submission and secure storage.
Version:     1.0
Author:      Dinesh: Zyon Studios
Author URI:  https://www.zyonstudios.co.uk/
License:     GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: job-application-plugin
*/

// Activation hook
register_activation_hook( __FILE__, 'job_application_plugin_activate' );

// Deactivation hook
register_deactivation_hook( __FILE__, 'job_application_plugin_deactivate' );

// Plugin activation callback
function job_application_plugin_activate() {
    // Perform necessary setup tasks when the plugin is activated
}

// Plugin deactivation callback
function job_application_plugin_deactivate() {
    // Perform cleanup tasks when the plugin is deactivated
}


// Function to add custom columns in the admin panel for job applications
function job_application_admin_columns( $columns ) {
    $new_columns = array(
        'applicant_email' => __( 'Email', 'job-application-plugin' ),
        'applicant_message' => __( 'Message', 'job-application-plugin' ), // New column for the message
        'applicant_cv'    => __( 'CV', 'job-application-plugin' ),
    );

    return array_merge( $columns, $new_columns );
}
add_filter( 'manage_job_application_posts_columns', 'job_application_admin_columns' );

// Function to display custom meta data in the admin panel for job applications
function job_application_admin_custom_column( $column, $post_id ) {
    if ( $column === 'applicant_email' ) {
        $email = get_post_meta( $post_id, 'applicant_email', true );
        echo esc_html( $email );
    } elseif ( $column === 'applicant_message' ) { // Add condition for the "Message" column
        $message = get_post_meta( $post_id, 'applicant_message', true );
        echo esc_html( $message );
    } elseif ( $column === 'applicant_cv' ) {
        $cv_attachment_id = get_post_meta( $post_id, 'cv_attachment_id', true );

        if ( $cv_attachment_id ) {
            $cv_url = wp_get_attachment_url( $cv_attachment_id );
            echo '<a href="' . esc_url( $cv_url ) . '">Download CV</a>';
        } else {
            echo '-';
        }
    }
}
add_action( 'manage_job_application_posts_custom_column', 'job_application_admin_custom_column', 10, 2 );


// Function to add custom action links in the admin panel for job applications
function job_application_admin_row_actions( $actions, $post ) {
    if ( $post->post_type === 'job_application' ) {
        $applicant_email = get_post_meta( $post->ID, 'applicant_email', true );

        if ( $applicant_email ) {
            $reply_url = 'mailto:' . rawurlencode( $applicant_email );
            $actions['reply'] = '<a href="' . esc_url( $reply_url ) . '">Reply</a>';
        }
    }

    return $actions;
}
add_filter( 'post_row_actions', 'job_application_admin_row_actions', 10, 2 );


// Function to handle CV attachment and upload
function job_application_upload_cv( $file ) {
    $uploaded_file = wp_handle_upload( $file, array( 'test_form' => false ) );

    if ( isset( $uploaded_file['file'] ) ) {
        $attachment = array(
            'post_mime_type' => $uploaded_file['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file['name'] ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attachment_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );

        if ( ! is_wp_error( $attachment_id ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $uploaded_file['file'] );
            wp_update_attachment_metadata( $attachment_id, $attachment_data );

            return $attachment_id;
        }
    }

    return 0; // Return 0 if CV upload fails
}


// Register custom post type for Job Applications
function job_application_register_post_type() {
    $labels = array(
        'name'                  => _x( 'Job Applications', 'Post type general name', 'job-application-plugin' ),
        'singular_name'         => _x( 'Job Application', 'Post type singular name', 'job-application-plugin' ),
        'menu_name'             => _x( 'Job Applications', 'Admin Menu text', 'job-application-plugin' ),
        'name_admin_bar'        => _x( 'Job Application', 'Add New on Toolbar', 'job-application-plugin' ),
        'add_new'               => __( 'Add New', 'job-application-plugin' ),
        'add_new_item'          => __( 'Add New Job Application', 'job-application-plugin' ),
        'new_item'              => __( 'New Job Application', 'job-application-plugin' ),
        'edit_item'             => __( 'Edit Job Application', 'job-application-plugin' ),
        'view_item'             => __( 'View Job Application', 'job-application-plugin' ),
        'all_items'             => __( 'All Job Applications', 'job-application-plugin' ),
        'search_items'          => __( 'Search Job Applications', 'job-application-plugin' ),
        'not_found'             => __( 'No job applications found', 'job-application-plugin' ),
        'not_found_in_trash'    => __( 'No job applications found in Trash', 'job-application-plugin' ),
        'featured_image'        => _x( 'Featured Image', 'Overrides the "Featured Image" phrase for this post type. Added in 4.3', 'job-application-plugin' ),
        'set_featured_image'    => _x( 'Set featured image', 'Overrides the "Set featured image" phrase for this post type. Added in 4.3', 'job-application-plugin' ),
        'remove_featured_image' => _x( 'Remove featured image', 'Overrides the "Remove featured image" phrase for this post type. Added in 4.3', 'job-application-plugin' ),
        'use_featured_image'    => _x( 'Use as featured image', 'Overrides the "Use as featured image" phrase for this post type. Added in 4.3', 'job-application-plugin' ),
        'archives'              => _x( 'Job Application archives', 'The post type archive label used in nav menus. Default "Post Archives". Added in 4.4', 'job-application-plugin' ),
        'insert_into_item'      => _x( 'Insert into job application', 'Overrides the "Insert into item"/"Insert into post" phrase (used when inserting media into a post). Added in 4.4', 'job-application-plugin' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this job application', 'Overrides the "Uploaded to this post" phrase (used when viewing media attached to a post). Added in 4.4', 'job-application-plugin' ),
        'filter_items_list'     => _x( 'Filter job applications list', 'Screen reader text for the filter links heading on the post type listing screen. Default "Filter posts list"/"Filter pages list". Added in 4.4', 'job-application-plugin' ),
        'items_list_navigation' => _x( 'Job applications list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default "Posts list navigation"/"Pages list navigation". Added in 4.4', 'job-application-plugin' ),
        'items_list'            => _x( 'Job applications list', 'Screen reader text for the items list heading on the post type listing screen. Default "Posts list"/"Pages list". Added in 4.4', 'job-application-plugin' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // Set to false to hide from the front-end.
        'publicly_queryable' => false, // Set to false to prevent front-end queries.
        'show_ui'            => true, // Show the post type in the admin panel.
        'show_in_menu'       => true, // Show the post type in the WordPress menu.
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'job-application' ), // Change the slug to your preferred URL.
        'capability_type'    => 'post',
        'has_archive'        => false, // Set to false if you don't need an archive page for job applications.
        'hierarchical'       => false,
        'menu_position'      => 5, // Adjust the menu position.
        'supports'           => array( 'title' ), // You can add other support fields if needed.
    );

    register_post_type( 'job_application', $args );
}
add_action( 'init', 'job_application_register_post_type' );

// Function to register the custom "Replied" taxonomy
function job_application_register_replied_taxonomy() {
    $labels = array(
        'name' => _x( 'Replied', 'taxonomy general name', 'job-application-plugin' ),
        'singular_name' => _x( 'Replied', 'taxonomy singular name', 'job-application-plugin' ),
        'search_items' => __( 'Search Replied', 'job-application-plugin' ),
        'all_items' => __( 'All Replied', 'job-application-plugin' ),
        'edit_item' => __( 'Edit Replied', 'job-application-plugin' ),
        'update_item' => __( 'Update Replied', 'job-application-plugin' ),
        'add_new_item' => __( 'Add New Replied', 'job-application-plugin' ),
        'new_item_name' => __( 'New Replied Name', 'job-application-plugin' ),
        'menu_name' => __( 'Replied', 'job-application-plugin' ),
    );

    $args = array(
        'hierarchical' => false,
        'labels' => $labels,
        'public' => false, // Set to false to hide the taxonomy from public view
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'replied' ), // Customize the taxonomy slug as desired
    );

    register_taxonomy( 'replied', 'job_application', $args );
}
add_action( 'init', 'job_application_register_replied_taxonomy' );


// Function to add custom "Set as Replied" bulk action
function job_application_add_bulk_action( $bulk_actions ) {
    $bulk_actions['mark_replied'] = __( 'Set as Replied', 'job-application-plugin' );
    return $bulk_actions;
}
add_filter( 'bulk_actions-edit-job_application', 'job_application_add_bulk_action' );



// Function to handle "Set as Replied" bulk action
function job_application_bulk_action_handler() {
    // Check if the bulk action is "mark_replied"
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'mark_replied' ) {
        // Get the selected job application IDs from the checkbox input
        $job_application_ids = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : array();

        // Loop through the selected job application IDs
        foreach ( $job_application_ids as $post_id ) {
            // Assign the "Replied" taxonomy to each job application post
            wp_set_post_terms( $post_id, 'replied', 'replied', true );
        }

        // Redirect back to the admin page after updating
        wp_safe_redirect( add_query_arg( 'bulk_replied', count( $job_application_ids ), admin_url( 'edit.php?post_type=job_application' ) ) );
        exit;
    }
}
add_action( 'admin_action_mark_replied', 'job_application_bulk_action_handler' );




// Shortcode callback function for the job application form
function job_application_form_shortcode() {
    // Generate and store a unique nonce
    $nonce = wp_create_nonce( 'job_application_nonce' );
    $_SESSION['job_application_nonce'] = $nonce;
    
    ob_start(); // Start output buffering

    // Check if the form has been submitted
    if ( isset( $_POST['submit_job_application'] ) ) {
        
        // Validate the nonce
        if ( isset( $_POST['job_application_nonce'] ) && wp_verify_nonce( $_POST['job_application_nonce'], 'job_application_nonce' ) ) {
            // The nonce is valid, proceed with form processing
            
            $current_timestamp = time();
            $previous_timestamp = isset( $_SESSION['job_application_timestamp'] ) ? (int) $_SESSION['job_application_timestamp'] : 0;

            // Check if the previous form submission was within the last X seconds (e.g., 10 seconds)
            $time_interval = 10; // Adjust the time interval as needed
            if ( $current_timestamp - $previous_timestamp >= $time_interval ) {
                // Store the current timestamp in the session
                $_SESSION['job_application_timestamp'] = $current_timestamp;
                

        // Validate form data (you can add more validation as needed)
        $name = sanitize_text_field( $_POST['applicant_name'] );
        $job = sanitize_text_field( $_POST['post_title'] );
        $email = sanitize_email( $_POST['applicant_email'] );
        $message = sanitize_textarea_field( $_POST['message'] );

//message
              $htmlContent = '<h2>Applicant Detail!</h2>
                    <p><b>Name:</b> '.$name.'</p>
                    <p><b>Email:</b> '.$email.'</p>
                    <p><b>Subject:</b> New application for-'.$job.'</p>
                    <p><b>Message:</b> '.$message.'</p>';
                    
        // Check if a CV file has been uploaded
        $cv_attachment_id = 0; // Initialize the attachment ID to 0
        if ( ! empty( $_FILES['cv_attachment']['name'] ) ) {
            // Handle CV attachment and upload process (see step 5)
            $cv_attachment_id = job_application_upload_cv( $_FILES['cv_attachment'] );
        }

        // Create a new job application post
        $post_args = array(
            'post_type'   => 'job_application',
            'post_title'  => $name . ' - application for-' . $job, // Customize the post title as needed
            'post_status' => 'publish',
            'meta_input'  => array(
                'applicant_email'   => $email,
                'applicant_message' => $message,
                'cv_attachment_id'  => $cv_attachment_id,
            ),
        );

        $post_id = wp_insert_post( $post_args );

        if ( ! is_wp_error( $post_id ) ) {
            // Fetch the CV attachment path
            $cv_attachment_path = get_attached_file( $cv_attachment_id );
             $toEmail = 'to@yoursite.com';
              

            // Set up the email parameters
            $to = $toEmail; // Replace with the recipient's email address
           // $cc = '' ; // Replace with the CC email address (optional)
           // $bcc = '';
            $subject = "New application for '.$job.'"; // Replace with the email subject
            $message = $htmlContent;
            

            // Set the headers for the email with CV attachment
            $headers = array(
                'From: ' . $name . ' <' . $email . '>',
                'Content-Type: text/html; charset=UTF-8', // Use this header for HTML emails
            );
            
            // Add CC and BCC headers if they are provided
            if ( ! empty( $cc ) ) {
                $headers[] = 'Cc: ' . $cc;
            }
            if ( ! empty( $bcc ) ) {
                $headers[] = 'Bcc: ' . $bcc;
            }

            // Send the email with attachment using wp_mail()
            $result = wp_mail( $to, $subject, $message, $headers, $cv_attachment_path );

            // Check if the email was sent successfully
            if ( $result ) {
                // Success message
                echo '<p class="job-application-success">Application submitted successfully!</p>';
            } else {
                echo 'Failed to send email.';
            }
        } else {
            // Error message
            echo '<p class="job-application-error">Failed to submit application. Please try again.</p>';
        }
        
            } else {
                // Multiple submissions within a short time interval
                echo '<p class="job-application-error">Please wait a moment before submitting again.</p>';
            }
        
        
        }
        else {
            // Invalid nonce or multiple submissions
            echo '<p class="job-application-error">Invalid form submission. Please try again.</p>';
        }
    }

    // Include the template file with the form
    include plugin_dir_path( __FILE__ ) . 'templates/job_application_form.php';

    return ob_get_clean(); // Return the form HTML and clear the output buffer
}

add_shortcode( 'job_application_form', 'job_application_form_shortcode' );

