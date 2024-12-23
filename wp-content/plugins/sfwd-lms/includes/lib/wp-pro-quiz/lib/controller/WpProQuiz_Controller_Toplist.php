<?php
/**
 * Quiz top list controller.
 *
 * @package LearnDash\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz top list controller.
 */
class WpProQuiz_Controller_Toplist extends WpProQuiz_Controller_Controller {

	public function route() {

		$quizId = $_GET['id'];
		$action = isset( $_GET['action'] ) ? $_GET['action'] : 'show';

		switch ( $action ) {
			case 'load_toplist':
				$this->loadToplist( $quizId );
				break;
			case 'show':
			default:
				$this->showAdminToplist( $quizId );
				break;
		}
	}

	private function loadToplist( $quizId ) {
		if ( ! current_user_can( 'wpProQuiz_toplist_edit' ) ) {
			echo wp_json_encode( array() );
			return;
		}

		$toplistMapper = new WpProQuiz_Model_ToplistMapper();
		$j             = array( 'data' => array() );
		$limit         = (int) $this->_post['limit'];
		$start         = $limit * ( $this->_post['page'] - 1 );
		$isNav         = isset( $this->_post['nav'] );

		if ( isset( $this->_post['a'] ) ) {
			switch ( $this->_post['a'] ) {
				case 'deleteAll':
					$toplistMapper->delete( $quizId );
					break;
				case 'delete':
					if ( ! empty( $this->_post['toplistIds'] ) ) {
						$toplistMapper->delete( $quizId, $this->_post['toplistIds'] );
					}
					break;
			}

			$start = 0;
			$isNav = true;
		}

		$toplist = $toplistMapper->fetch( $quizId, $limit, $this->_post['sort'], $start );

		foreach ( $toplist as $tp ) {
			$j['data'][] = array(
				'id'     => $tp->getToplistId(),
				'name'   => $tp->getName(),
				'email'  => $tp->getEmail(),
				'type'   => $tp->getUserId() ? 'R' : 'UR',
				'date'   => WpProQuiz_Helper_Until::convertTime(
					$tp->getDate(),
					LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Quizzes_Management_Display', 'toplist_time_format' )
				),
				'points' => $tp->getPoints(),
				'result' => $tp->getResult(),
			);
		}

		if ( $isNav ) {

			$count    = $toplistMapper->count( $quizId );
			$pages    = ceil( $count / $limit );
			$j['nav'] = array(
				'count' => $count,
				'pages' => $pages ? $pages : 1,
			);
		}

		echo wp_json_encode( $j );
	}

	private function editAdminToplist() {
		$toplistId = $this->_post['toplistId'];
		$username  = trim( $this->_post['name'] );
		$email     = trim( $this->_post['email'] );

		if ( empty( $name ) || empty( $email ) || filter_var( $email, FILTER_VALIDATE_EMAIL ) === false ) {
			return array( 'error' => esc_html__( 'No name or e-mail entered.', 'learndash' ) );
		}

	}

	private function showAdminToplist( $quizId ) {
		if ( ! current_user_can( 'wpProQuiz_toplist_edit' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'learndash' ) );
		}

		$view       = new WpProQuiz_View_AdminToplist();
		$quizMapper = new WpProQuiz_Model_QuizMapper();

		$quiz = $quizMapper->fetch( $quizId );

		$view->quiz = $quiz;
		$view->show();
	}

	public function getAddToplist( WpProQuiz_Model_Quiz $quiz ) {
		$userId = get_current_user_id();

		if ( ! $quiz->isToplistActivated() ) {
			return null;
		}

		$data = array(
			'userId' => $userId,
			'token'  => wp_create_nonce( 'wpProQuiz_toplist' ),
			'canAdd' => $this->preCheck( $quiz->getToplistDataAddPermissions(), $userId ),
		);

		if ( $quiz->isToplistDataCaptcha() && 0 == $userId ) {
			$captcha = WpProQuiz_Helper_Captcha::getInstance();

			if ( $captcha->isSupported() ) {
				$data['captcha']['img']  = WPPROQUIZ_CAPTCHA_URL . '/' . $captcha->createImage();
				$data['captcha']['code'] = $captcha->getPrefix();
			}
		}

		return $data;
	}

	public function addInToplist() {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		} else {
			$user_id = 0;
		}

		if ( ( isset( $this->_post['results'] ) ) && ( ! empty( $this->_post['results'] ) ) && ( is_string( $this->_post['results'] ) ) ) {
			$this->_post['results'] = json_decode( stripslashes( $this->_post['results'] ), true );
		}

		$include_admin_in_reports = true;
		if ( ! empty( $user_id ) ) {
			if ( learndash_is_admin_user( $user_id ) ) {
				$include_admin_in_reports = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_General_Admin_User', 'reports_include_admin_users' );
				if ( 'yes' == $include_admin_in_reports ) {
					$include_admin_in_reports = true;
				} else {
					$include_admin_in_reports = false;
				}
			} else {
				$include_admin_in_reports = true;
			}

			// For logged in users to allow an override filter.
			/**
			 * Filters whether to include admin users in the quiz toplist report or not.
			 *
			 * @param boolean $include_admin_in_reports Whether to include admin user in toplist report.
			 * @param int     $user_id                  User ID.
			 * @param array   $name                     Global post data.
			 */
			$include_admin_in_reports = apply_filters( 'learndash_quiz_add_toplist_bypass', $include_admin_in_reports, $user_id, $this->_post );
		}

		if ( $include_admin_in_reports ) {
			$quizId     = isset( $this->_post['quizId'] ) ? $this->_post['quizId'] : 0;
			$prefix     = ! empty( $this->_post['prefix'] ) ? trim( $this->_post['prefix'] ) : '';
			$quizMapper = new WpProQuiz_Model_QuizMapper();

			$quiz = $quizMapper->fetch( $quizId );

			$r = $this->handleAddInToplist( $quiz );

			if ( $quiz->isToplistActivated() && $quiz->isToplistDataCaptcha() && get_current_user_id() == 0 ) {
				$captcha = WpProQuiz_Helper_Captcha::getInstance();

				if ( $captcha->isSupported() ) {
					$captcha->remove( $prefix );
					$captcha->cleanup();

					if ( true !== $r ) {
						$r['captcha']['img']  = WPPROQUIZ_CAPTCHA_URL . '/' . $captcha->createImage();
						$r['captcha']['code'] = $captcha->getPrefix();
					}
				}
			}
		} else {
			$r = true;
		}

		if ( true === $r ) {
			$r = array(
				'text'  => esc_html__( 'You signed up successfully.', 'learndash' ),
				'clear' => true,
			);
		}

		echo wp_json_encode( $r );
	}

	/**
	 * Handles adding in a top list.
	 *
	 * @param WpProQuiz_Model_Quiz $quiz Quiz model.
	 *
	 * @return array{text: string, clear: bool}|true Array on error, true on success.
	 */
	private function handleAddInToplist( WpProQuiz_Model_Quiz $quiz ) {
		if ( ! wp_verify_nonce( $this->_post['token'], 'wpProQuiz_toplist' ) ) {
			return array(
				'text'  => esc_html__( 'An error has occurred.', 'learndash' ),
				'clear' => true,
			);
		}

		$quizId       = $quiz->getId();
		$userId       = get_current_user_id();
		$quiz_post_id = (int) $this->_post['quiz'];

		if ( ! isset( $this->_post['results'] ) ) {
			return array(
				'text'  => esc_html__( 'An error has occurred.', 'learndash' ),
				'clear' => true,
			);
		}

		$points                = 0;
		$total_possible_points = 0;
		$total_awarded_points  = 0;

		$results = $this->_post['results'];
		if ( ! empty( $results ) ) {

			foreach ( $results as $r_idx => $result ) {
				if ( 'comp' == $r_idx ) {
					$points = learndash_format_course_points( $total_awarded_points );
					continue;
				}

				$points_array = array(
					'points'         => learndash_format_course_points( $result['points'] ),
					'correct'        => intval( $result['correct'] ),
					'possiblePoints' => learndash_format_course_points( $result['possiblePoints'] ),
				);
				if ( false === $points_array['correct'] ) {
					$points_array['correct'] = 0;
				} elseif ( true === $points_array['correct'] ) {
					$points_array['correct'] = 1;
				}
				$points_str = maybe_serialize( $points_array );

				if ( ! wp_verify_nonce( $result['p_nonce'], 'ld_quiz_pnonce' . $userId . '_' . $quizId . '_' . $quiz_post_id . '_' . $r_idx . '_' . $points_str ) ) {
					$this->_post['results'][ $r_idx ]['points']         = 0;
					$this->_post['results'][ $r_idx ]['correct']        = 0;
					$this->_post['results'][ $r_idx ]['possiblePoints'] = 0;
				}
				$total_awarded_points  += learndash_format_course_points( $this->_post['results'][ $r_idx ]['points'] );
				$total_possible_points += learndash_format_course_points( $this->_post['results'][ $r_idx ]['possiblePoints'] );
			}
		}

		$name          = ! empty( $this->_post['name'] ) ? trim( $this->_post['name'] ) : '';
		$email         = ! empty( $this->_post['email'] ) ? trim( $this->_post['email'] ) : '';
		$ip            = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
		$captchaAnswer = ! empty( $this->_post['captcha'] ) ? trim( $this->_post['captcha'] ) : '';
		$prefix        = ! empty( $this->_post['prefix'] ) ? trim( $this->_post['prefix'] ) : '';
		$quizMapper    = new WpProQuiz_Model_QuizMapper();
		$toplistMapper = new WpProQuiz_Model_ToplistMapper();

		if ( null == $quiz || 0 == $quiz->getId() || ! $quiz->isToplistActivated() ) {
			return array(
				'text'  => esc_html__( 'An error has occurred.', 'learndash' ),
				'clear' => true,
			);
		}

		if ( ! $this->preCheck( $quiz->getToplistDataAddPermissions(), $userId ) ) {
			return array(
				'text'  => esc_html__( 'An error has occurred.', 'learndash' ),
				'clear' => true,
			);
		}

		$clearTime = null;

		if ( $quiz->isToplistDataAddMultiple() ) {
			$clearTime = $quiz->getToplistDataAddBlock() * 60;
		}

		if ( $userId > 0 ) {
			if ( $toplistMapper->countUser( $quizId, $userId, $clearTime ) ) {
				return array(
					'text'  => esc_html__( 'You can not enter again.', 'learndash' ),
					'clear' => true,
				);
			}

			$user  = wp_get_current_user();
			$email = $user->user_email;
			$name  = $user->display_name;

		} else {
			if ( $toplistMapper->countFree( $quizId, $name, $email, $ip, $clearTime ) ) {
				return array(
					'text'  => esc_html__( 'You can not enter again.', 'learndash' ),
					'clear' => true,
				);
			}

			if ( empty( $name ) || empty( $email ) || filter_var( $email, FILTER_VALIDATE_EMAIL ) === false ) {
				return array(
					'text'  => esc_html__( 'No name or e-mail entered.', 'learndash' ),
					'clear' => false,
				);
			}

			if ( strlen( $name ) > 15 ) {
				return array(
					'text'  => esc_html__( 'Your name can not exceed 15 characters.', 'learndash' ),
					'clear' => false,
				);
			}

			if ( $quiz->isToplistDataCaptcha() ) {
				$captcha = WpProQuiz_Helper_Captcha::getInstance();

				if ( $captcha->isSupported() ) {
					if ( ! $captcha->check( $prefix, $captchaAnswer ) ) {
						return array(
							'text'  => esc_html__( 'You entered wrong captcha code.', 'learndash' ),
							'clear' => false,
						);
					}
				}
			}
		}

		if ( $total_possible_points > 0 ) {
			$result = round( $points / $total_possible_points * 100, 2 );
		} else {
			$result = 0;
		}

		$toplist = new WpProQuiz_Model_Toplist();
		$toplist->setQuizId( $quizId )
			->setUserId( $userId )
			->setDate( time() )
			->setName( $name )
			->setEmail( $email )
			->setPoints( $points )
			->setResult( $result )
			->setIp( $ip );

		$toplistMapper->save( $toplist );

		return true;
	}

	private function preCheck( $type, $userId ) {
		switch ( $type ) {
			case WpProQuiz_Model_Quiz::QUIZ_TOPLIST_TYPE_ALL:
				return true;
			case WpProQuiz_Model_Quiz::QUIZ_TOPLIST_TYPE_ONLY_ANONYM:
				return 0 == $userId;
			case WpProQuiz_Model_Quiz::QUIZ_TOPLIST_TYPE_ONLY_USER:
				return $userId > 0;
		}

		return false;
	}

	public function showFrontToplist() {
		$quizIds       = empty( $this->_post['quizIds'] ) ? array() : array_unique( (array) $this->_post['quizIds'] );
		$toplistMapper = new WpProQuiz_Model_ToplistMapper();
		$quizMapper    = new WpProQuiz_Model_QuizMapper();
		$r             = array();
		$j             = array();

		foreach ( $quizIds as $quizId ) {
			$quiz = $quizMapper->fetch( $quizId );
			if ( null == $quiz || 0 == $quiz->getId() ) {
				continue;
			}

			$toplist = $toplistMapper->fetch( $quizId, $quiz->getToplistDataShowLimit(), $quiz->getToplistDataSort() );

			foreach ( $toplist as $tp ) {
				$j[ $quizId ][] = array(
					'name'   => $tp->getName(),
					'date'   => WpProQuiz_Helper_Until::convertTime(
						$tp->getDate(),
						LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Quizzes_Management_Display', 'toplist_time_format' )
					),
					'points' => $tp->getPoints(),
					'result' => $tp->getResult(),
				);
			}
		}

		echo wp_json_encode( $j );
	}
}
