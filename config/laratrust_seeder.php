<?php

return [
    'truncate_tables' => true,

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],

    /**
     * Struktur Roles dan Permissions
     */
    'roles_structure' => [
        
        'superadministrator' => [
            'users' => 'c,r,u,d',
            'roles' => 'c,r,u,d',
            'courses' => 'c,r,u,d',
            'materials' => 'c,r,u,d', 
            'course_student' => 'c,r,u,d',
            'weeks' => 'c,r,u,d',
        ],
        
        'admin' => [
            'users' => 'c,r,u,d',
            'courses' => 'c,r,u,d',
            'materials' => 'c,r,u,d', 
            'course_student' => 'c,r,u,d',
            'weeks' => 'c,r,u,d',
        ],
        
        'pengajar' => [
            'courses' => 'c,r,u,d',
            'materials' => 'c,r,u,d', 
            'course_student' => 'r',
            'weeks' => 'c,r,u,d',
        ],
        
        'siswa' => [
            'courses' => 'r',         
            'materials' => 'r', 
            'course_student' => 'c,r',
            'weeks' => 'r',
        ],
    ],
];