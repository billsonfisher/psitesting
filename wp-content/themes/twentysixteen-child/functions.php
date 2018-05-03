<?php

/* PREVENT THE USER FROM ACCESSING THE WP REGISTRATION PAGE - THIS DIRECTS THEM TO THE PROPER WEBSITE REGISTRATION PAGE */
function my_registration_page_redirect()
{
    global $pagenow;
    if ( ( strtolower($pagenow) == 'wp-login.php') && ( strtolower( $_GET['action']) == 'register' ) ) {
        wp_redirect( home_url('/purchase-your-practice-tests-here'));
    }
}
add_filter( 'init', 'my_registration_page_redirect' );

/* PREVENT THE USER FROM ACCESSING THE WP LOGIN PAGE - THIS DIRECTS THEM TO THE PROPER WEBSITE LOGIN PAGE */
function my_login_page_redirect()
{
    global $pagenow;
 if( 'wp-login.php' == $pagenow ) {
        wp_redirect( home_url('/sign-in'));
    }
}
add_filter( 'init', 'my_login_page_redirect' );

/* THIS REDIRECTS THE USER WHEN THEY HAVE SUCCESSFULLY LOGGED IN */
add_filter( 'login_redirect', 'ckc_login_redirect' );
function ckc_login_redirect() {
    // Change this to the url to Updates page.
    return home_url( '/my-test-dashboard' );
}

/* THIS CHANGES THE TEXT IN THE SUBMIT BOX FOR THE PAID MEMBER SUBSCRIPTION REGISTRATION PAGE */
add_filter('pms_register_form_submit_text', 'pmsc_change_register_submit_text');
function pmsc_change_register_submit_text() {
	return 'Continue';
}

/* THIS CHANGES THE TEXT IN THE SUBMIT BOX FOR THE PAID MEMBER SUBSCRIPTION NEW SUBSCRIPTION PAGE */
add_filter('pms_new_subscription_form_submit_text', 'pmsc_change_new_subscription_submit_text');
function pmsc_change_new_subscription_submit_text() {
	return 'Continue';
}

//Wp-pro-Quiz-customization 
add_shortcode( 'useresults', 'get_user_results_quiz' );

function get_user_results_quiz($atts){
    ob_start();
    global $wpdb;
    $currentuserid = get_current_user_id();
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wp_pro_quiz_statistic_ref Join {$wpdb->prefix}wp_pro_quiz_master ON {$wpdb->prefix}wp_pro_quiz_master.id={$wpdb->prefix}wp_pro_quiz_statistic_ref.quiz_id WHERE  user_id = {$currentuserid}", OBJECT );
    ?>
      <div class="results-box">
          <table class="table table-results">
              <tr>
                  <th>Quiz Name</th>
                  <th>Date</th>
                  <th>View Result</th>
              </tr>
            <?php
               foreach ($results as $key => $value) {
                   ?>
                   <tr>
                       <td><?php echo $value->name; ?></td>
                       <td><?php echo date('Y-m-d a',$value->create_time); ?></td>
                       <td><a href="javascript:void(0);" data-refid="<?php echo $value->statistic_ref_id; ?>" data-quizid="<?php echo $value->quiz_id; ?>">Result</a></td>
                   </tr>
                   <?php
               }
            ?>
          </table>
          <div class="catch-ajax-response"></div>
      </div>
      <?php
    
    return ob_get_clean();
}



function custom_js_quiz() { 
  
    wp_enqueue_script( 'custom-js-quiz', get_stylesheet_directory_uri() . '/js/custom-js-quiz.js', array(), '', true );
    wp_localize_script( 'custom-js-quiz', 'ajax_object_custom',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_enqueue_scripts', 'custom_js_quiz' );

add_action('wp_ajax_wp_custom_quiz_get', 'custom_quiz_ajax_callback');
add_action('wp_ajax_nopriv_wp_custom_quiz_get','custom_quiz_ajax_callback');

function custom_quiz_ajax_callback() {
        global $wpdb;
    
        $quizId = $_POST['quizid'];
        $userId = $_POST['userid'];
        $refId = $_POST['refId'];
        
        $avg = false;
        $refIdUserId = $avg ? $userId : $refId;

        $statisticRefMapper = new WpProQuiz_Model_StatisticRefMapper();
        $statisticUserMapper = new WpProQuiz_Model_StatisticUserMapper();
        $formMapper = new WpProQuiz_Model_FormMapper();

        $statisticUsers = $statisticUserMapper->fetchUserStatistic($refIdUserId, $quizId, $avg);

        $output = array();
       


        foreach ($statisticUsers as $statistic) {
            /* @var $statistic WpProQuiz_Model_StatisticUser */

            if (!isset($output[$statistic->getCategoryId()])) {
                $output[$statistic->getCategoryId()] = array(
                    'questions' => array(),
                    'categoryId' => $statistic->getCategoryId(),
                    'categoryName' => $statistic->getCategoryId() ? $statistic->getCategoryName() : __('No category',
                        'wp-pro-quiz')
                );
            }

            $o = &$output[$statistic->getCategoryId()];

            $o['questions'][] = array(
                'correct' => $statistic->getCorrectCount(),
                'incorrect' => $statistic->getIncorrectCount(),
                'hintCount' => $statistic->getIncorrectCount(),
                'time' => $statistic->getQuestionTime(),
                'points' => $statistic->getPoints(),
                'gPoints' => $statistic->getGPoints(),
                'questionid'=>$statistic->getQuestionId(),
                'statistcAnswerData' => $statistic->getStatisticAnswerData(),
                'questionName' => $statistic->getQuestionName(),
                'questionAnswerData' => $statistic->getQuestionAnswerData(),
                'answerType' => $statistic->getAnswerType(),
                'solvedCount' => $statistic->getSolvedCount()
            );
        }

        $view = new WpProQuiz_View_StatisticsAjax();

        $view->avg = $avg;
        $view->statisticModel = $statisticRefMapper->fetchByRefId($refIdUserId, $quizId, $avg);

        $view->userName = __('Anonymous', 'wp-pro-quiz');

        if ($view->statisticModel->getUserId()) {
            $userInfo = get_userdata($view->statisticModel->getUserId());

            if ($userInfo !== false) {
                $view->userName = $userInfo->user_login . ' (' . $userInfo->display_name . ')';
            } else {
                $view->userName = __('Deleted user', 'wp-pro-quiz');
            }
        }

        if (!$avg) {
            $view->forms = $formMapper->fetch($quizId);
        }

        $view->userStatistic = $output;

        $html = $view->getUserTable();
        $result = $view->grandresult;
       
      
        $getresulttextresult = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wp_pro_quiz_master  WHERE  id = {$quizId}", OBJECT );
        $graduationarray = unserialize($getresulttextresult[0]->result_text);
        $prozentgraduation=$graduationarray['prozent'];
        $graduationtext = $graduationarray['text'];
        $ky = '';
       foreach ($prozentgraduation as $key => $value) {
         if($result < $value){
              $ky = $key - 1;
              break;
         }
       }
       $graphresult = $graduationtext[$ky];
       wp_send_json_success( array('html'=>$html,'result'=>$result,'ky'=>$graphresult) );

    wp_die(); // this is required to terminate immediately and return a proper response
}

function loginuserandauthenticate( $userdata ) {
  $creds = array(
        'user_login'    => $userdata['user_login'],
        'user_password' => $userdata['user_pass'],
        'remember'      => true
    );
 
   $user = wp_signon( $creds, false );
    if ( !is_wp_error( $user ) ) {
     
       wp_set_current_user( $user->ID, $user->user_login );
    }
 

}
add_action( 'pms_register_form_after_create_user', 'loginuserandauthenticate', 10, 1 );

add_filter( 'pms_get_redirect_url', 'change_redirect_url_pms', 10, 2 );

function change_redirect_url_pms($url, $location){
  $newurl = get_permalink(1681);
  return $newurl;
}
?>