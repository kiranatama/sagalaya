SAGALAYA PHP-FRAMEWORK
======================

Framework build based on lithium (https://github.com/UnionOfRAD/lithium), integrating Twig as View layer and Doctrine as Model layer. 
Using Doctrine and Twig building web application become more intuitive.

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

e.g Model filtering use-case :
------------------------------
<pre>
	<code>
		$users = User::findAll(array(
			'where' => array(
				'and' => array(
					array('fullname' => array('neq' => 'someone')),
					array('created' => array('gte' => '2010-10-10'))
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