# Valid-example-01
Very simple implementation of Valid using php.

1) generate your P12 using admin.time4mind.com
   choose a password and put it in a *safe* place

2) convert P12 to PEM format using the bash script 
   (curl likes the PEM format)

3) put PEM file in a *safe* place 
   (NOT under the web documet root!)

4) customize the config.php with your *safe* PEM path 
   and your *secret* password

That's all!


