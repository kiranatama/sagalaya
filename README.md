SAGALAYA PHP-FRAMEWORK
======================

Framework build based on lithium (https://github.com/UnionOfRAD/lithium), integrating Twig as View layer and Doctrine as Model layer. 
Using Doctrine and Twig building web application become more intuitive.

Installation instruction 
-----------------------------

- Clone the framework to your application directory e.g <code> git clone https://github.com/kiranatama/sagalaya.git app_dir </code>
- Change your connection setting (host/login/password/database) at <code> app/config/bootstrap/connections.php </code> 
- Change default root url ('/') at <code> app/config/routes.php </code>, default is redirected to Pages::view
- Gain an overview with reading basic tutorial on running app


e.g Model creation use-case :
-----------------------------
<pre>
	<code>
		$user = new User($this->request->data);
		
		if ($user->save()) {
			$this->redirect('Users::index');
		} else {
			$errors = $user->getErrors();
		}
		
		return compact('user', 'errors');
	</code>
</pre>

e.g Model Access use-case :
------------------------------
<pre>
	<code>
		$user = User::findOneById($id);
		$other = User::get($other_id);
		
		// access user repository
		$lastUser = User::getLastUserLogin();
	</code>
</pre>

e.g Model filtering use-case :
------------------------------
<pre>
	<code>
		$users = User::findAll(array(
			'where' => array(
				'and' => array(
					array('fullname' => array('neq' => 'someone')),
					array('created' => array('gte' => '2010-10-10'))
				), 
				'or' => array(
					array('public' => array('eq' => true))
				)
			),
			'leftJoin' => array(
				array('field' => 'profile',
					'leftJoin' => array(
						'field' => 'nationality',
						'where' => array(
							array('name' => array('eq' => 'Indonesia'))
						)
					)
				)
			)
		));
	</code>
</pre>

e.g View use-case :
-------------------
<pre>
	<code>
		<strong>{{ user.fullname }}</strong>
		created : {{ user.created.format('M d, h:i') }}
		
		{% for user in users %}
			{{ user.fullname }}, {{ user.email }}
		{% endfor %} 
	</code>
</pre>
