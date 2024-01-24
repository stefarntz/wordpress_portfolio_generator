<?php
/*
Plugin Name: Portfolio generator
Description: generate portfolios with customizable buttons and empty pages.
Version: 1.0.2
*/

// Portfolio generation function
function generate_portfolio($button_names) {
    $page_titles = array('Portfolio');

    // Add user-defined button names to the page titles array
    foreach ($button_names as $name) {
        $page_titles[] = $name;
    }

    // Get the admin email
    $admin_email = get_option('admin_email');

    // Create empty pages
    foreach ($page_titles as $title) {
        $new_page = array(
            'post_title'    => $title,
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page'
        );

        // Insert the page into the database
        $page_id = wp_insert_post($new_page);

        // Check if the page title is 'Contact' to generate the contact form
        if ($title === 'Contact') {
            $contact_form = '
            <form method="post" action="">
                <label for="contact_name">Your Name:</label><br>
                <input type="text" id="contact_name" name="contact_name" required><br><br>
                <label for="contact_email">Your Email:</label><br>
                <input type="email" id="contact_email" name="contact_email" required><br><br>
                <label for="contact_message">Message:</label><br>
                <textarea id="contact_message" name="contact_message" rows="4" required></textarea><br><br>
                <input type="submit" name="submit_contact" value="Submit">
            </form>';

            // Save the form markup in post content
            $page_data = array(
                'ID'           => $page_id,
                'post_content' => $contact_form,
            );

            wp_update_post($page_data);
        }
    }

    // Handle form submission
    if (isset($_POST['submit_contact'])) {
        $name = sanitize_text_field($_POST['contact_name']);
        $email = sanitize_email($_POST['contact_email']);
        $message = sanitize_textarea_field($_POST['contact_message']);

        // Process the form data and send the email to the admin
        $to = $admin_email; // Send the email to the admin who generated the pages
        $subject = 'Contact Form Submission';
        $mail_body = "Name: $name\nEmail: $email\nMessage: $message";
        $headers = 'From: ' . $email;

        wp_mail($to, $subject, $mail_body, $headers);

        // Display a success message or perform other actions upon form submission
        echo '<div class="alertbar">Message Sent!</div>';
    } else {
        // Output a message indicating successful portfolio generation
        echo '<h2 style="width: 250px; padding: 10px 20px; background-color: #46B450; color: white;">Portfolio generated successfully!</h2>';
    }
}

// Admin menu for portfolio generation
function custom_portfolio_plugin_menu() {
    add_menu_page(
        'Generate Portfolio',
        'Generate Portfolio',
        'manage_options',
        'generate-portfolio',
        'generate_portfolio_page', // Function to handle portfolio generation page
        'dashicons-portfolio',
        99
    );
}

// Hook into the admin menu
add_action('admin_menu', 'custom_portfolio_plugin_menu');

// Function to generate portfolio page in admin
function generate_portfolio_page() {
    if (isset($_POST['submit'])) {
        $button_names = array();

        // Get button names from form input
        for ($i = 1; $i <= $_POST['button_count']; $i++) {
            $button_name = sanitize_text_field($_POST['button' . $i]);
            if (!empty($button_name)) {
                $button_names[] = $button_name;
            }
        }

        // If no button names were provided, set default values
        if (empty($button_names)) {
            $button_names = array('About me', 'My skills', 'Contact');
        }

        // Generate portfolio with provided button names
        generate_portfolio($button_names);
    }
    ?>
    <style>
        /* Add rounded corners to buttons */
        #addButton,
        [type="submit"] {
            border-radius: 5px;
        }
    </style>
    <div class="wrap">
        <h2>Generate Portfolio</h2>
        <h4>If values are not changed, default values will be used</h4>
        <form method="post" style="max-width: 500px;">
            <?php
            $default_button_names = array('About me', 'My skills', 'Contact');
            for ($i = 1; $i <= count($default_button_names); $i++) {
                ?>
                <label for="button<?php echo $i; ?>">Button <?php echo $i; ?> Name:</label>
                <input type="text" name="button<?php echo $i; ?>" id="button<?php echo $i; ?>" value="<?php echo isset($_POST['button' . $i]) ? $_POST['button' . $i] : $default_button_names[$i - 1]; ?>"><br><br>
                <?php
            }
            ?>

            <div id="buttonFields"></div>

            <button type="button" id="addButton" style="background-color: #0073AA; color: white; padding: 8px 16px; border: none; cursor: pointer; border-radius: 5px;">

Add Button</button><br><br>

            <input type="submit" name="submit" value="Generate Portfolio" style="background-color: #0073AA; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;">
            <input type="hidden" id="button_count" name="button_count" value="<?php echo count($default_button_names); ?>">
        </form>
    </div>

    <script>
        document.getElementById('addButton').addEventListener('click', function() {
            const buttonFields = document.getElementById('buttonFields');
            const buttonCount = document.querySelectorAll('input[type="text"]').length + 1;

            if (buttonCount <= 9) {
                const newLabel = document.createElement('label');
                newLabel.setAttribute('for', `button${buttonCount}`);
                newLabel.textContent = `Button ${buttonCount} Name:`;

                const newInput = document.createElement('input');
                newInput.setAttribute('type', 'text');
                newInput.setAttribute('name', `button${buttonCount}`);
                newInput.setAttribute('id', `button${buttonCount}`);
                newInput.setAttribute('style', `margin-left: 5px;`);
                newInput.setAttribute('placeholder', `Enter Button ${buttonCount} Name`);

                buttonFields.appendChild(newLabel);
                buttonFields.appendChild(newInput);
                buttonFields.appendChild(document.createElement('br'));
                buttonFields.appendChild(document.createElement('br'));

                document.getElementById('button_count').value = buttonCount;
            } else {
                document.getElementById('addButton').disabled = true;
            }
        });
    </script>
    <?php
}
?>