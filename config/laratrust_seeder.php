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
            'staff_prodis' => 'c,r,u,d',
            'pengajars' => 'c,r,u,d',
            'siswas' => 'c,r,u,d', 
            
            // Master Data Organisasi
            'faculties' => 'c,r,u,d',
            'departments' => 'c,r,u,d',
            'study_programs' => 'c,r,u,d',

            // Akademik
            'courses' => 'c,r,u,d',
            'course_classes' => 'c,r,u,d',
            'weeks' => 'c,r,u,d',
            'materials' => 'c,r,u,d',
            'manage_enrollment_status' => 'c,r,u,d',
            'assignments' => 'c,r,u,d',
            'submissions' => 'c,r,u,d',
            'exams' => 'c,r,u,d',
            'questions' => 'c,r,u,d',
        ],

        'admin' => [
            'users' => 'c,r,u,d',
            'staff_prodis' => 'c,r,u,d',
            'pengajars' => 'c,r,u,d',
            'siswas' => 'c,r,u,d',

            'faculties' => 'c,r,u,d',
            'departments' => 'c,r,u,d',
            'study_programs' => 'c,r,u,d',
            
            'courses' => 'c,r,u,d',
            'course_classes' => 'c,r,u,d',
            'weeks' => 'c,r,u,d',
            'materials' => 'c,r,u,d',
            'manage_enrollment_status' => 'c,r,u,d',
            'assignments' => 'c,r,u,d',
            'submissions' => 'c,r,u,d',
            'exams' => 'c,r,u,d',
            'questions' => 'c,r,u,d',
        ],

        'staff_prodi' => [
            'users' => 'c,r,u,d',
            'pengajars' => 'c,r,u,d',
            'siswas' => 'c,r,u,d',
            
            // Akademik (Di lingkup prodinya)
            'courses' => 'c,r,u,d',
            'course_classes' => 'c,r,u,d', 
            'weeks' => 'c,r,u,d', 
            'materials' => 'c,r,u,d',
            'assignments' => 'c,r,u,d',
            'submissions' => 'r', 
        ],

        'pengajar' => [
            'courses' => 'r',
            'course_classes' => 'r',
            'weeks' => 'c,r,u,d',
            'materials' => 'c,r,u,d',
            'assignments' => 'c,r,u,d',
            'submissions' => 'r,u',
            'exams' => 'c,r,u,d',
            'questions' => 'c,r,u,d',
        ],
        
        'siswa' => [        
            'course_classes' => 'r',
            'weeks' => 'r',
            'materials' => 'r',
            'assignments' => 'r',
            'submissions' => 'c,r,u,d',
            'exams' => 'r',
            'questions' => 'r',
        ],
    ],
];