# PDOhelper

PDO to Stored procedures helper classes.

CRUD operations with stored procedures for MySQL and SQLSRV.

Install stored procedures from sql folder.

Install with composer.json with require in your project

<pre>
{
    "require": {
        "mainsim/PDOhelper": "1.0"
    },
    "repositories": [
        {
	      "type": "package",
	      "package": {
	        "name": "mainsim/PDOhelper",
	        "version": "1.0",
	        "source": {
	          "url": "https://github.com/mainsim/PDOhelper.git",
	          "type": "git",
		  "reference": "master"
	        },
	        "autoload": {
		        "psr-4": {
		            "mainsim\\pdohelper\\": "src/"
		        }
		    }
	      }
	    }
    ]
}
</pre>
