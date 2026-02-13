<?php
$modules = [
    'prime' => [
        'id'     => 'div_prog1',
        'titre'  => 'Module Nombre premier',
        'scripts' => [
		['name' => 'prime', 'file' => 'prime.py'],
		['name' => 'prime_improve', 'file' => 'prime_improve.py'],
		['name' => 'prime_dynamic_load_balancer', 'file' => 'prime_dynamic_load_balancer.py'],
		['name' => 'prime_DLB_improve', 'file' => 'prime_DLB_improve.py']
	],
        'inputs' => [
            ['name' => 'nb_prim', 'label' => 'Nombre premier', 'type' => 'number']
        ]
    ],
    'mc' => [
        'id'     => 'div_prog2',
        'titre'  => 'Module Pi Montecarlo',
        'scripts' => [
		['name' => 'MonteCarlo MPI Python', 'file' => 'MC/MC_MPI_worker_master.py'],
		['name' => 'MonteCarlo Java', 'file' => 'MC/Projet_Java/distributedMC_step1/Master_worker']
	],
        'inputs' => [
            ['name' => 'nb_pts', 'label' => 'Nombre de points', 'type' => 'number']
        ]
    ],
    'integral' => [
        'id'     => 'div_prog3',
        'titre'  => 'Module Intégrale Simpson',
        'script' => ['MasterSimpsonSocket.java','WorkerSimpsonSocket.java'],
        'inputs' => [
            ['name' => 'a', 'label' => 'Borne A', 'type' => 'number'],
            ['name' => 'b', 'label' => 'Borne B', 'type' => 'number'],
            ['name' => 'n', 'label' => 'Nb segments', 'type' => 'number'],
            ['name' => 'mu', 'label' => 'µ', 'type' => 'number'],
            ['name' => 'sigma', 'label' => 'σ', 'type' => 'number']
        ]
    ]
];
