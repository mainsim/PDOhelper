<?php

require_once dirname(__FILE__).'/vendor/autoload.php';

use mainsim\pdohelper\Factory;
use mainsim\pdohelper\Utils;

Factory::connect(['driver' => 'PDO',
            'engine' => 'MYSQL',
            'database' => 'yourDB',
            'host' => 'localhost',
            'user' => 'root', 
            'password' => ''
        ]);

#crud for stored procedures

$pdo = new Utils();

$pdo->select([
            'tables' => ['t_creation_date as t1'],
            'fields' => ['*'],
            'join' => [
                [
                    'type' => 'LEFT',
                    'table' => 't_workorders as t2',
                    'condition' => [
                        ['t2.f_code', '=', 't1.f_id'],
                        ['t1.fc_editor_user_mail', 'LIKE', 'galanti@mainsim.com']
                    ],
                    'operator' => ['AND']
                ],[
                    'type' => 'LEFT',
                    'table' => 't_custom_fields as t3',
                    'condition' => [
                        ['t3.f_code', '=', 't2.f_code']
                    ]
                ]
            ],
            'condition' => [
                         ['t1.f_id', '=', 1]
            ]
        ]);

$pdo->insert([
        'table' => 't_wf_groups',
        'fields' => [
                     'f_group' => 'Wayn',
                     'f_order' => 9,
                     'f_visibility' => -1
                     ],
         'increment' => 'f_id'
        ]);

$pdo->insertMultiple([
        'table' => 't_wf_groups',
        'fields' => ['f_group', 'f_order', 'f_visibility'],
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
        'table' => 't_wf_groups',
        'fields' => [
                     'f_group' => 'Tiger'
                     ],
        'condition' => [
                        ['f_id', '=', 9],
                        ['f_visibility', '=', -1],
                        ['f_order', '=', 9]
                ],
        'operator' => ['AND', 'AND'] // 'operator' => ['(AND)', 'OR']
        ]);

    
$pdo->delete([
             'table' => 't_wf_groups',
             'condition' => [
                             ['f_id', '>', 8]
                             ]
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
$pdo->isInDb('t_creation_date'); 

#return true if field exists in table
$pdo->isInDb('t_creation_date', 'f_id'); 

#return true if type exists in table field type list
$pdo->isInDb('t_creation_date', 'f_id', 'string'); 

#return type if type exists in table field type list
$pdo->isInDb('t_creation_date', 'f_id', '', true); 



?>
