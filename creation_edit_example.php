<?
    function create() {
		$this->subtitle = "Nuevo usuario";
        $this->item = new Users();
        
		if($this->params['post']['sm'] != 1) return;
		
        $post = $this->params['post']['item'];
        
		$this->item->dui   = $post['dui'];
		$this->item->fullname   = $post['fullname'];
        $this->item->email      = $post['email'];
        $this->item->password   = $post['password'];
		$this->item->token  = Crypto::md5(Crypto::random_str(100).uniqid().time());
        //$this->item->timezone   = $post['timezone'];
		$this->item->role       = $post['role'];
		
		$v_dui = Users::find_by_sql("SELECT count(*) as c FROM users where dui = {$this->item->dui}");
		//$crypto_pass = $this->item->password;
		//$crypto_mail = $this->item->email;
		if($v_dui[0]->c > 0 or $v_dui[0]->c < 0) {
			$this->error("Ya existe ese número de cedula");
			return;
		}

		if(!$this->item->save()) {
			$this->error("Hay campos incompletos, por favor verifique.");
			return;
		}

		$this->updatePass($this->item->email, $this->item->password);
		$this->notice("Usuario creado exitosamente.");
		$this->redirect(WWW_PATH . '/users');
	}

    function edit() {
		$this->subtitle = "Editar usuario";		
		
		//TRAEMOS LOS DATOS DEL USUARIO A EDITAR MEDIANTE GET
		$this->item = Users::find($this->params['get']['id']);
		
		//PARA ENVIAR LOS DATOS UNA VEZ FUERON MODIFICADOS O NO
		
		//SI ES DIFENTE A 1 NO EDITA
		if($this->params['post']['sm'] != 1) return;
        
		//RECIBIMOS LOS DATOS EDITADOS DEL FORMULARIO DEFINIDO EN LA VISTA MEDIANTE POST
		$item = $this->params['post']['item'];
        if ($item['role'] != null && $item['role'] != '') {
			$params = array(
				'fullname'      => $item['fullname'],
				'email'         => $item['email'],
				'password'      => $item['password'],
				'role'          => $item['role']
			);
	    } else {
			$params = array(
				'fullname'      => $item['fullname'],
				'email'         => $item['email'],
				'password'      => $item['password']
			);
		}
        	
		if(!$this->item->update_attributes($params)) {
			$this->error("Hay campos incompletos, por favor verifique.");
			return;
		}

		$this->updatePass($this->item->email, $this->item->password);
	
		$this->notice("Usuario editado exitosamente.");
		$this->redirect(WWW_PATH . '/users/');
	}

 	private function updatePass($email, $pass){
		$l_login = Users::find_by_sql("SELECT CONVERT(last_login, CHAR) AS last_log FROM users WHERE email = '{$email}'");
		$newpass = Crypto::md5(Crypto::md5($pass).$l_login[0]->last_log); //the way is crypted is the same as in the auth
		Users::execute(" UPDATE users SET password = '{$newpass}' WHERE email = '{$email}' ");
	}//function that updates the password to be encrypted 


?>