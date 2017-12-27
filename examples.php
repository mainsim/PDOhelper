<?php

require_once dirname(__FILE__).'/vendor/autoload.php';

use mainsim\pdohelper\PDOProcedureHelper;

$db = ['driver' => 'PDO',
            'engine' => 'MYSQL',
            'database' => 'yourDB',
            'host' => 'localhost',
            'user' => 'root', 
            'password' => ''
        ];

#crud for stored procedures

$pdo = new PDOProcedureHelper($db);

$pdo->select([
            'tables' => ['table_a as t1'],
            'fields' => ['*'],
            'join' => [
                [
                    'type' => 'LEFT',
                    'table' => 'table_b as t2',
                    'condition' => [
                        ['t2.id', '=', 't1.id'],
                        ['t1.email', 'LIKE', 'xyz@mainsim.com']
                    ],
                    'operator' => ['AND']
                ],[
                    'type' => 'LEFT',
                    'table' => 'table_c as t3',
                    'condition' => [
                        ['t3.id', '=', 't2.id']
                    ]
                ]
            ],
            'condition' => [
                         ['t1.id', '=', 1]
            ]
        ]);

$pdo->insert([
        'table' => 'groups',
        'fields' => [
                     'group' => 'Wayn',
                     'order' => 9,
                     'visibility' => -1
                     ],
         'increment' => 'f_id'
        ]);

$pdo->insertMultiple([
        'table' => 'roups',
        'fields' => ['group', 'order', 'visibility'],
        'data' => [
                ['John', 10, -1],  
                ['Wayne', 11, -1],
                ['Clint', 11, -1],
                ['Eastwood', 11, -1],
                ['Western', 11, -1]         
        ],
        'increment' => 'f_id'
    ]);

$pdo->update([
        'table' => 'groups',
        'fields' => [
                     'group' => 'Tiger'
                     ],
        'condition' => [
                        ['id', '=', 9],
                        ['visibility', '=', -1],
                        ['order', '=', 9]
                ],
        'operator' => ['AND', 'AND'] // 'operator' => ['(AND)', 'OR']
        ]);

    
$pdo->delete([
             'table' => 'groups',
             'condition' => [
                             ['id', '>', 8]
                             ],
             'increment' => 'id'
        ]);

$pdo->addColumns([
    'table' => 'test',
    'cols' => [
        'bla' => 'varchar(30) not null',
        'blabla' => 'int(11)'
    ]
]);


#return array table/columns/types 
$pdo->isInDb(); 
// alias 
$pdo->getTableColumns() // no parameters

#return true if table exists
$pdo->isInDb('table_a'); 

#return true if field exists in table
$pdo->isInDb('table_a', 'id'); 

#return true if type exists in table field type list
$pdo->isInDb('table_a', 'id', 'string'); 

#return type if type exists in table field type list
$pdo->isInDb('table_a', 'id', '', true); 



?>
