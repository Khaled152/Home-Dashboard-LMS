<?php
/**
 * Dashboard Administration Screen
 *
 * @package WordPress
 * @subpackage Administration
 */

/** Load WordPress Bootstrap */
require_once __DIR__ . '/admin.php';

/** Load WordPress dashboard API */
require_once ABSPATH . 'wp-admin/includes/dashboard.php';

wp_dashboard_setup();

wp_enqueue_script( 'dashboard' );

if ( current_user_can( 'install_plugins' ) ) {
	wp_enqueue_script( 'plugin-install' );
	wp_enqueue_script( 'updates' );
}
if ( current_user_can( 'upload_files' ) ) {
	wp_enqueue_script( 'media-upload' );
}
add_thickbox();

if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
}

// Used in the HTML title tag.
$title       = __( 'Dashboard' );
$parent_file = 'index.php';

$help  = '<p>' . __( 'Welcome to your WordPress Dashboard!' ) . '</p>';
$help .= '<p>' . __( 'The Dashboard is the first place you will come to every time you log into your site. It is where you will find all your WordPress tools. If you need help, just click the &#8220;Help&#8221; tab above the screen title.' ) . '</p>';

$screen = get_current_screen();

$screen->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' => $help,
	)
);

// Help tabs.

$help  = '<p>' . __( 'The left-hand navigation menu provides links to all of the WordPress administration screens, with submenu items displayed on hover. You can minimize this menu to a narrow icon strip by clicking on the Collapse Menu arrow at the bottom.' ) . '</p>';
$help .= '<p>' . __( 'Links in the Toolbar at the top of the screen connect your dashboard and the front end of your site, and provide access to your profile and helpful WordPress information.' ) . '</p>';

$screen->add_help_tab(
	array(
		'id'      => 'help-navigation',
		'title'   => __( 'Navigation' ),
		'content' => $help,
	)
);

$help  = '<p>' . __( 'You can use the following controls to arrange your Dashboard screen to suit your workflow. This is true on most other administration screens as well.' ) . '</p>';
$help .= '<p>' . __( '<strong>Screen Options</strong> &mdash; Use the Screen Options tab to choose which Dashboard boxes to show.' ) . '</p>';
$help .= '<p>' . __( '<strong>Drag and Drop</strong> &mdash; To rearrange the boxes, drag and drop by clicking on the title bar of the selected box and releasing when you see a gray dotted-line rectangle appear in the location you want to place the box.' ) . '</p>';
$help .= '<p>' . __( '<strong>Box Controls</strong> &mdash; Click the title bar of the box to expand or collapse it. Some boxes added by plugins may have configurable content, and will show a &#8220;Configure&#8221; link in the title bar if you hover over it.' ) . '</p>';

$screen->add_help_tab(
	array(
		'id'      => 'help-layout',
		'title'   => __( 'Layout' ),
		'content' => $help,
	)
);

$help = '<p>' . __( 'The boxes on your Dashboard screen are:' ) . '</p>';

if ( current_user_can( 'edit_theme_options' ) ) {
	$help .= '<p>' . __( '<strong>Welcome</strong> &mdash; Shows links for some of the most common tasks when setting up a new site.' ) . '</p>';
}

if ( current_user_can( 'view_site_health_checks' ) ) {
	$help .= '<p>' . __( '<strong>Site Health Status</strong> &mdash; Informs you of any potential issues that should be addressed to improve the performance or security of your website.' ) . '</p>';
}

if ( current_user_can( 'edit_posts' ) ) {
	$help .= '<p>' . __( '<strong>At a Glance</strong> &mdash; Displays a summary of the content on your site and identifies which theme and version of WordPress you are using.' ) . '</p>';
}

$help .= '<p>' . __( '<strong>Activity</strong> &mdash; Shows the upcoming scheduled posts, recently published posts, and the most recent comments on your posts and allows you to moderate them.' ) . '</p>';

if ( is_blog_admin() && current_user_can( 'edit_posts' ) ) {
	$help .= '<p>' . __( "<strong>Quick Draft</strong> &mdash; Allows you to create a new post and save it as a draft. Also displays links to the 3 most recent draft posts you've started." ) . '</p>';
}

$help .= '<p>' . sprintf(
	/* translators: %s: WordPress Planet URL. */
	__( '<strong>WordPress Events and News</strong> &mdash; Upcoming events near you as well as the latest news from the official WordPress project and the <a href="%s">WordPress Planet</a>.' ),
	__( 'https://planet.wordpress.org/' )
) . '</p>';

$screen->add_help_tab(
	array(
		'id'      => 'help-content',
		'title'   => __( 'Content' ),
		'content' => $help,
	)
);

unset( $help );

$wp_version = get_bloginfo( 'version', 'display' );
/* translators: %s: WordPress version. */
$wp_version_text = sprintf( __( 'Version %s' ), $wp_version );
$is_dev_version  = preg_match( '/alpha|beta|RC/', $wp_version );

if ( ! $is_dev_version ) {
	$version_url = sprintf(
		/* translators: %s: WordPress version. */
		esc_url( __( 'https://wordpress.org/documentation/wordpress-version/version-%s/' ) ),
		sanitize_title( $wp_version )
	);

	$wp_version_text = sprintf(
		'<a href="%1$s">%2$s</a>',
		$version_url,
		$wp_version_text
	);
}

$screen->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://wordpress.org/documentation/article/dashboard-screen/">Documentation on Dashboard</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/forums/">Support forums</a>' ) . '</p>' .
	'<p>' . $wp_version_text . '</p>'
);

require_once ABSPATH . 'wp-admin/admin-header.php';
?>

<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<?php
	if ( ! empty( $_GET['admin_email_remind_later'] ) ) :
		/** This filter is documented in wp-login.php */
		$remind_interval = (int) apply_filters( 'admin_email_remind_interval', 3 * DAY_IN_SECONDS );
		$postponed_time  = get_option( 'admin_email_lifespan' );

		/*
		 * Calculate how many seconds it's been since the reminder was postponed.
		 * This allows us to not show it if the query arg is set, but visited due to caches, bookmarks or similar.
		 */
		$time_passed = time() - ( $postponed_time - $remind_interval );

		// Only show the dashboard notice if it's been less than a minute since the message was postponed.
		if ( $time_passed < MINUTE_IN_SECONDS ) :
			$message = sprintf(
				/* translators: %s: Human-readable time interval. */
				__( 'The admin email verification page will reappear after %s.' ),
				human_time_diff( time() + $remind_interval )
			);
			wp_admin_notice(
				$message,
				array(
					'type'        => 'success',
					'dismissible' => true,
				)
			);
		endif;
	endif;
	?>

<?php
if ( has_action( 'welcome_panel' ) && current_user_can( 'edit_theme_options' ) ) :
	$classes = 'welcome-panel';

	$option = (int) get_user_meta( get_current_user_id(), 'show_welcome_panel', true );
	// 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner.
	$hide = ( 0 === $option || ( 2 === $option && wp_get_current_user()->user_email !== get_option( 'admin_email' ) ) );
	if ( $hide ) {
		$classes .= ' hidden';
	}
	?>

	<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
		<?php wp_nonce_field( 'welcome-panel-nonce', 'welcomepanelnonce', false ); ?>
		<a class="welcome-panel-close" href="<?php echo esc_url( admin_url( '?welcome=0' ) ); ?>" aria-label="<?php esc_attr_e( 'Dismiss the welcome panel' ); ?>"><?php _e( 'Dismiss' ); ?></a>
		<?php
		/**
		 * Fires when adding content to the welcome panel on the admin dashboard.
		 *
		 * To remove the default welcome panel, use remove_action():
		 *
		 *     remove_action( 'welcome_panel', 'wp_welcome_panel' );
		 *
		 * @since 3.5.0
		 */
		do_action( 'welcome_panel' );
		?>
	</div>
<?php endif; ?>

	<div id="dashboard-widgets-wrap">
		<!-------------------------------------------------------------->
		<?php

// Get the total number of published courses
$total_courses = new WP_Query([
    'post_type'   => 'courses',
    'post_status' => 'publish',
    'posts_per_page' => -1,
]);
$total_courses_count = $total_courses->found_posts;

// Get the total number of students (assuming 'subscriber' is the role for students)
$total_students = count(get_users([
    'role' => 'subscriber',
]));

// Get the total number of published lessons
$total_lessons = new WP_Query([
    'post_type'   => 'lesson',
    'post_status' => 'publish',
    'posts_per_page' => -1,
]);
$total_lessons_count = $total_lessons->found_posts;

// Get the total number of enrollments (assuming '_is_tutor_student' meta key indicates enrollment)
$enrollments_query = new WP_User_Query([
    'meta_key' => '_is_tutor_student',
    'meta_value' => '1', // Assuming 1 means enrolled
]);
$enrollments_count = $enrollments_query->get_total();

// Get course count per month and by category
global $wpdb;

$monthly_courses = [];
$courses_by_category = [];

// Get the monthly course creation count
$monthly_courses_results = $wpdb->get_results("
    SELECT DATE_FORMAT(post_modified, '%Y-%m') AS month, COUNT(*) AS course_count
    FROM $wpdb->posts
    WHERE post_type = 'courses' AND post_status = 'publish'
    GROUP BY DATE_FORMAT(post_modified, '%Y-%m')
");

foreach ($monthly_courses_results as $result) {
    $monthly_courses[$result->month] = $result->course_count;
}

// Get courses by category count
$courses_by_category_results = $wpdb->get_results("
    SELECT t.name as category, COUNT(tr.object_id) AS course_category_count
    FROM $wpdb->terms t
    JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id
    JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
    JOIN $wpdb->posts p ON tr.object_id = p.ID
    WHERE p.post_type = 'courses' AND p.post_status = 'publish'
    GROUP BY t.term_id
");

foreach ($courses_by_category_results as $result) {
    $courses_by_category[$result->category] = $result->course_category_count;
}
?>

<!-- Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="tutor-row tutor-gx-4">
    <!-- Course Data Cards -->
    <?php
    $data = [
        ['icon' => 'mortarboard-o', 'value' => $total_courses_count, 'label' => 'Published Courses'],
        ['icon' => 'add-member', 'value' => $enrollments_count, 'label' => 'Course Enrolled'],
        ['icon' => 'book-open', 'value' => $total_lessons_count, 'label' => 'Lessons'],
        ['icon' => 'user-graduate', 'value' => $total_students, 'label' => 'Students'],
    ];
    foreach ($data as $item) {
        echo '<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
                <div class="tutor-card tutor-card-secondary tutor-p-24">
                    <div class="tutor-d-flex">
                        <div class="tutor-round-box">
                            <span class="tutor-icon-' . esc_attr($item['icon']) . '" area-hidden="true"></span>
                        </div>
                        <div class="tutor-ml-20">
                            <div class="tutor-fs-4 tutor-fw-bold tutor-color-black">' . esc_html($item['value']) . '</div>
                            <div class="tutor-fs-7 tutor-color-secondary">' . esc_html($item['label']) . '</div>
                        </div>
                    </div>
                </div>
            </div>';
    }
    ?>
</div>

<div class="tutor-row tutor-gx-4">
	
<div class="tutor-col-md-4 tutor-col-xl-8 tutor-my-8 tutor-my-md-16">
        <canvas id="coursesPerMonthChart"></canvas>
    </div>

    <div class="tutor-col-md-4 tutor-col-xl-4 tutor-my-8 tutor-my-md-16">
        <canvas id="coursesCreatedChart"></canvas>
    </div>

   

</div>
<div class="tutor-row tutor-gx-4">
<div class="tutor-col-md-4 tutor-col-xl-8 tutor-my-8 tutor-my-md-16" >
<?php
$user        = wp_get_current_user();
$time_period = $active = isset( $_GET['period'] ) ? $_GET['period'] : '';
$start_date  = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
$end_date    = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
$popular_courses = tutor_utils()->most_popular_courses( 7, get_current_user_id() );
        $reviews         = tutor_utils()->get_reviews_by_instructor( $user->ID, $offset = 0, 7 );
        ?>


    <?php if ( count( $popular_courses ) ) : ?>
        <div class="tutor-analytics-widget tutor-analytics-widget-popular-courses tutor-mb-32 "  id="Mostcourses" >
            <div class="tutor-analytics-widget-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
                <?php esc_html_e( 'Most Popular Courses', 'tutor-pro' ); ?>
            </div>
            <div class="tutor-analytics-widget-body">
                <div class="tutor-table-responsive">
                    <table class="tutor-table">
                        <thead>
                            <th width="70%">
                                <?php esc_html_e( 'Course Name', 'tutor-pro' ); ?>
                            </th>
                            <th width="15%">
                                <?php esc_html_e( 'Total Enrolled', 'tutor-pro' ); ?>
                            </th>
                            <th width="15%">
                                <?php esc_html_e( 'Rating', 'tutor-pro' ); ?>
                            </th>
                        </thead>


                        <tbody>
                            <?php foreach ( $popular_courses as $course ) : ?>
                                <tr>
                                    <td>
                                        <?php esc_html_e( $course->post_title ); ?>
                                    </td>
                                    <td>
                                        <?php esc_html_e( $course->total_enrolled ); ?>
                                    </td>
                                    <td>
                                        <?php
                                            $rating     = tutor_utils()->get_course_rating( $course->ID );
                                            $avg_rating = ! is_null( $rating ) ? $rating->rating_avg : 0;
                                        ?>
                                        <?php tutor_utils()->star_rating_generator_v2( $avg_rating, null, true ); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
	<div class="tutor-col-md-4 tutor-col-xl-4 tutor-my-8 tutor-my-md-16">
        <canvas id="coursesByCategoryChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const coursesCreatedData = {
        labels: ['Courses Created', 'Lessons Created'],
        datasets: [{
            data: [<?php echo esc_js($total_courses_count); ?>, <?php echo esc_js($total_lessons_count); ?>],
            backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)'],
            borderColor: ['rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)'],
            borderWidth: 1
        }]
    };

    new Chart(document.getElementById('coursesCreatedChart').getContext('2d'), {
        type: 'doughnut',
        data: coursesCreatedData,
        options: { responsive: true, maintainAspectRatio: false }
    });

    const coursesPerMonthData = {
        labels: <?php echo json_encode(array_keys($monthly_courses)); ?>,
        datasets: [{
            label: 'Courses Created',
            data: <?php echo json_encode(array_values($monthly_courses)); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };

    new Chart(document.getElementById('coursesPerMonthChart').getContext('2d'), {
        type: 'bar',
        data: coursesPerMonthData,
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    const coursesByCategoryData = {
        labels: <?php echo json_encode(array_keys($courses_by_category)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($courses_by_category)); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    };

    new Chart(document.getElementById('coursesByCategoryChart').getContext('2d'), {
        type: 'doughnut',
        data: coursesByCategoryData,
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>

<style>
    #coursesCreatedChart, #coursesPerMonthChart, #coursesByCategoryChart, .tutor-card, #Mostcourses {
        background-color: white !important;
        padding: 17px !important;
        border-radius: 18px !important;
        box-shadow: rgba(17, 17, 26, 0.1) 0px 0px 16px;
    }
	#Mostcourses, #coursesPerMonthChart{
	margin-left: 5px;
	}
</style>


	
<!------------------------------------------------------------------------------------------>
</div>

	</div><!-- dashboard-widgets-wrap -->

</div><!-- wrap -->

<?php
wp_print_community_events_templates();

require_once ABSPATH . 'wp-admin/admin-footer.php';