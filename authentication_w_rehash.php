<?

class AuthController extends ApplicationController {
	
	protected function before_filter() {
		parent::before_filter();
		
		switch($this->params['action']) {
			case 'logout':
			break;
			
			default:
			if(Session::get('user')) $this->redirect(WWW_PATH);
			break;
		}
	}
	
	function index() {
		$this->title = "Ingresar";
	}

    function login() {
		$user = Users::first(array('conditions' => $this->userWPass($this->params['post']['email'],
		$this->params['post']['password'])));/*I used a function because the Framework only allows 2 posts, 
												and has to make an array out of them*/
		if(!$user) {
			$this->error("Email o contraseña inválida");
			$this->redirect(WWW_PATH . "/auth");
			return;
		}

		$flag = false;
		if($user->has_role(Users::ACCESS_ADMIN)) {
			$flag = true;
		}elseif($user->has_role(Users::ACCESS_TEACHER)) {
			$flag = true;
		}elseif($user->has_role(Users::ACCESS_STUDENT)){
			$flag = true;	
		}

		if(!flag){
			$this->error("No tienes acceso al sistema.");
			$this->redirect(WWW_PATH . "/auth");
			return;
		}
		
		$this->updateToken($this->params['post']['email']); //here you update the token,
		$this->lastLogin($this->params['post']['email']); //here the login date
		$this->updatePass($this->params['post']['email'], $this->params['post']['password']);//and here the password's hash
		Session::set('user', $user->id);
		$this->notice("Bienvenido de vuelta!");
		$this->redirect(WWW_PATH);
	} 
	
	function logout() {
		Session::remove('user');
		$this->redirect(WWW_PATH.'/auth/');
	} 

 	private function updateToken($email){
		$newtoken = Crypto::md5(Crypto::random_str(100).uniqid().time());
		Users::execute(" UPDATE users SET token = '{$newtoken}' WHERE email = '{$email}' ");
	} //updates the token of an user

 	private function lastLogin($email){
		Users::execute(" UPDATE users SET last_login = CURRENT_TIMESTAMP() WHERE email = '{$email}' ");
	} //Updates the login date of an user

	private function userWPass($mail, $pswd){//this function receives the authentication posts
		$usr_arr = array('email' 	=> $mail, 
						'password' 	=> $this->getPass($mail, $pswd));//then hashes the password sn generates an array
		return $usr_arr;//after all that it returns the array with the email an password hashed
	}

	private function getPass($email, $pass){//function that hashes the password to let the user login
		$l_login = Users::find_by_sql("SELECT CONVERT(last_login, CHAR) AS last_log FROM users WHERE email = '{$email}'");
		$password = Crypto::md5(Crypto::md5($pass).$l_login[0]->last_log);
		/*you can crypt the pass however you want, as long as you do it the same way
		with your insert/update functions. I recomend using the token instead of the
		login date, since it'll be safer both for integrity and security reasons*/
		return $password;//returns hashed password
	}

	private function updatePass($email, $pass){
		$l_login = Users::find_by_sql("SELECT CONVERT(last_login, CHAR) AS last_log FROM users WHERE email = '{$email}'");
		$newpass = Crypto::md5(Crypto::md5($pass).$l_login[0]->last_log);
		Users::execute(" UPDATE users SET password = '{$newpass}' WHERE email = '{$email}' ");
	} //updates the password hash after an user logins

}
?>