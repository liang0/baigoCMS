<?php
return array(
    'prefer' => array(
        'editor' => array(
            'title' => 'WYSIWYG Editor',
            'lists'  => array(
                'default_height' => array(
                    'title'      => 'Default height',
                    'type'       => 'str',
                    'format'     => 'int',
                ),
                'resize' => array(
                    'title'      => 'Resize',
                    'type'       => 'switch',
                ),
                'autosize' => array(
                    'title'      => 'Auto resize',
                    'type'       => 'switch',
                ),
            ),
        ),
        'excerpt' => array(
            'title' => 'Article excerpt',
            'lists'  => array(
                'type' => array(
                    'title'     => 'Default mode',
                    'type'      => 'select',
                    'option'    => array(),
                ),
                'count' => array(
                    'title'      => 'Excerpt count',
                    'type'       => 'str',
                    'format'     => 'int',
                ),
            ),
        ),
        'sync' => array(
            'title' => 'Login sync',
            'lists'  => array(
                'sync' => array(
                    'title'      => 'Login sync',
                    'type'       => 'switch',
                ),
            ),
        ),
    ),
    'secqa' => array(
        'What is your grandmother&rsquo;s name?',
        'What is your grandfather&rsquo;s name?',
        'When is your birthday?',
        'What is your mother&rsquo;s name?',
        'What is your father&rsquo;s name?',
        'What is your license plate number?',
        'Where is your hometown?',
        'What is your pet&rsquo;s name?',
        'What is your primary school&rsquo;s name?',
        'What is your favorite color?',
        'What is your daughter&rsquo;s or son&rsquo;s nickname?',
        'Who is your best friend when you were a child?',
        'What is the name of your most respected teacher?',
    ),
);

