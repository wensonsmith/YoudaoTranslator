<?php

use Alfred\Workflows\Workflow;

class TestCase extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_add_a_result()
    {
        $workflow = new Workflow;

        $workflow->result()
                    ->uid('THE ID')
                    ->title('Item Title')
                    ->subtitle('Item Subtitle')
                    ->quicklookurl('https://www.google.com')
                    ->type('file')
                    ->arg('ARGUMENT')
                    ->valid(false)
                    ->icon('icon.png')
                    ->mod('cmd', 'Do Something Different', 'something-different')
                    ->mod('shift', 'Another Different', 'another-different', false)
                    ->copy('Please copy this')
                    ->largetype('This will be huge')
                    ->autocomplete('AutoComplete This');

        $expected = [
            'items' => [
                [
                    'arg'          => 'ARGUMENT',
                    'autocomplete' => 'AutoComplete This',
                    'icon'         => [
                        'path' => 'icon.png',
                    ],
                    'mods' => [
                        'cmd' => [
                            'subtitle' => 'Do Something Different',
                            'arg'      => 'something-different',
                            'valid'    => true,
                        ],
                        'shift' => [
                            'subtitle' => 'Another Different',
                            'arg'      => 'another-different',
                            'valid'    => false,
                        ],
                    ],
                    'quicklookurl' => 'https://www.google.com',
                    'subtitle'     => 'Item Subtitle',
                    'text'         => [
                        'copy'      => 'Please copy this',
                        'largetype' => 'This will be huge',
                    ],
                    'title'        => 'Item Title',
                    'type'         => 'file',
                    'uid'          => 'THE ID',
                    'valid'        => false,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->output());
    }

    /** @test */
    public function it_can_add_multiple_results()
    {
        $workflow = new Workflow;

        $workflow->result()
                    ->uid('THE ID')
                    ->title('Item Title')
                    ->subtitle('Item Subtitle')
                    ->quicklookurl('https://www.google.com')
                    ->type('file')
                    ->arg('ARGUMENT')
                    ->valid(false)
                    ->icon('icon.png')
                    ->mod('cmd', 'Do Something Different', 'something-different')
                    ->mod('shift', 'Another Different', 'another-different', false)
                    ->copy('Please copy this')
                    ->largetype('This will be huge')
                    ->autocomplete('AutoComplete This');

        $workflow->result()
                    ->uid('THE ID 2')
                    ->title('Item Title 2')
                    ->subtitle('Item Subtitle 2')
                    ->quicklookurl('https://www.google.com/2')
                    ->type('file')
                    ->arg('ARGUMENT 2')
                    ->valid(true)
                    ->icon('icon2.png')
                    ->mod('cmd', 'Do Something Different 2', 'something-different 2')
                    ->mod('shift', 'Another Different 2', 'another-different 2', false)
                    ->copy('Please copy this 2')
                    ->largetype('This will be huge 2')
                    ->autocomplete('AutoComplete This 2');

        $expected = [
            'items' => [
                [
                    'arg'          => 'ARGUMENT',
                    'autocomplete' => 'AutoComplete This',
                    'icon'         => [
                        'path' => 'icon.png',
                    ],
                    'mods' => [
                        'cmd' => [
                            'subtitle' => 'Do Something Different',
                            'arg'      => 'something-different',
                            'valid'    => true,
                        ],
                        'shift' => [
                            'subtitle' => 'Another Different',
                            'arg'      => 'another-different',
                            'valid'    => false,
                        ],
                    ],
                    'quicklookurl' => 'https://www.google.com',
                    'subtitle'     => 'Item Subtitle',
                    'text'         => [
                        'copy'      => 'Please copy this',
                        'largetype' => 'This will be huge',
                    ],
                    'title'        => 'Item Title',
                    'type'         => 'file',
                    'uid'          => 'THE ID',
                    'valid'        => false,
                ],
                [
                    'arg'          => 'ARGUMENT 2',
                    'autocomplete' => 'AutoComplete This 2',
                    'icon'         => [
                        'path' => 'icon2.png',
                    ],
                    'mods' => [
                        'cmd' => [
                            'subtitle' => 'Do Something Different 2',
                            'arg'      => 'something-different 2',
                            'valid'    => true,
                        ],
                        'shift' => [
                            'subtitle' => 'Another Different 2',
                            'arg'      => 'another-different 2',
                            'valid'    => false,
                        ],
                    ],
                    'quicklookurl' => 'https://www.google.com/2',
                    'subtitle'     => 'Item Subtitle 2',
                    'text'         => [
                        'copy'      => 'Please copy this 2',
                        'largetype' => 'This will be huge 2',
                    ],
                    'title'        => 'Item Title 2',
                    'type'         => 'file',
                    'uid'          => 'THE ID 2',
                    'valid'        => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->output());
    }

    /** @test */
    public function it_can_handle_a_file_skipcheck_via_arguments()
    {
        $workflow = new Workflow;

        $workflow->result()->type('file', false);

        $expected = [
            'items' => [
                [
                    'type'  => 'file:skipcheck',
                    'valid' => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->output());
    }

    /** @test */
    public function it_can_add_mods_via_shortcuts()
    {
        $workflow = new Workflow;

        $workflow->result()->cmd('Hit Command', 'command-it', false)
                            ->shift('Hit Shift', 'shift-it', true);

        $expected = [
            'items' => [
                [
                    'mods' => [
                        'cmd' => [
                            'subtitle' => 'Hit Command',
                            'arg'      => 'command-it',
                            'valid'    => false,
                        ],
                        'shift' => [
                            'subtitle' => 'Hit Shift',
                            'arg'      => 'shift-it',
                            'valid'    => true,
                        ],
                    ],
                    'valid' => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->output());
    }

    /** @test */
    public function it_can_handle_file_icon_via_shortcut()
    {
        $workflow = new Workflow;

        $workflow->result()->fileiconIcon('icon.png');

        $expected = [
            'items' => [
                [
                    'icon' => [
                        'path' => 'icon.png',
                        'type' => 'fileicon',
                    ],
                    'valid' => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->output());
    }

    /** @test */
    public function it_can_handle_file_type_via_shortcut()
    {
        $workflow = new Workflow;

        $workflow->result()->filetypeIcon('icon.png');

        $expected = [
            'items' => [
                [
                    'icon' => [
                        'path' => 'icon.png',
                        'type' => 'filetype',
                    ],
                    'valid' => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->output());
    }

    /** @test */
    public function it_can_sort_results_by_defaults()
    {
        $workflow = new Workflow;

        $workflow->result()
                    ->uid('THE ID')
                    ->title('Item Title')
                    ->subtitle('Item Subtitle');

        $workflow->result()
                    ->uid('THE ID 2')
                    ->title('Item Title 2')
                    ->subtitle('Item Subtitle 2');

        $expected = [
            'items' => [
                [
                    'subtitle'     => 'Item Subtitle',
                    'title'        => 'Item Title',
                    'uid'          => 'THE ID',
                    'valid'        => true,
                ],
                [
                    'subtitle'     => 'Item Subtitle 2',
                    'title'        => 'Item Title 2',
                    'uid'          => 'THE ID 2',
                    'valid'        => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->sortResults()->output());
    }

    /** @test */
    public function it_can_sort_results_desc()
    {
        $workflow = new Workflow;

        $workflow->result()
                    ->uid('THE ID')
                    ->title('Item Title')
                    ->subtitle('Item Subtitle');

        $workflow->result()
                    ->uid('THE ID 2')
                    ->title('Item Title 2')
                    ->subtitle('Item Subtitle 2');

        $expected = [
            'items' => [
                [
                    'subtitle'     => 'Item Subtitle 2',
                    'title'        => 'Item Title 2',
                    'uid'          => 'THE ID 2',
                    'valid'        => true,
                ],
                [
                    'subtitle'     => 'Item Subtitle',
                    'title'        => 'Item Title',
                    'uid'          => 'THE ID',
                    'valid'        => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->sortResults('desc')->output());
    }

    /** @test */
    public function it_can_sort_results_by_field()
    {
        $workflow = new Workflow;

        $workflow->result()
                    ->uid('456')
                    ->title('Item Title')
                    ->subtitle('Item Subtitle');

        $workflow->result()
                    ->uid('123')
                    ->title('Item Title 2')
                    ->subtitle('Item Subtitle 2');

        $expected = [
            'items' => [
                [
                    'subtitle'     => 'Item Subtitle 2',
                    'title'        => 'Item Title 2',
                    'uid'          => '123',
                    'valid'        => true,
                ],
                [
                    'subtitle'     => 'Item Subtitle',
                    'title'        => 'Item Title',
                    'uid'          => '456',
                    'valid'        => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->sortResults('asc', 'uid')->output());
    }

    /** @test */
    public function it_can_filter_results()
    {
        $workflow = new Workflow;

        $workflow->result()
                    ->uid('THE ID')
                    ->title('Item Title')
                    ->subtitle('Item Subtitle');

        $workflow->result()
                    ->uid('THE ID 2')
                    ->title('Item Title 2')
                    ->subtitle('Item Subtitle 2');

        $expected = [
            'items' => [
                [
                    'subtitle'     => 'Item Subtitle 2',
                    'title'        => 'Item Title 2',
                    'uid'          => 'THE ID 2',
                    'valid'        => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->filterResults(2)->output());
    }

    /** @test */
    public function it_can_filter_results_by_a_different_key()
    {
        $workflow = new Workflow;

        $workflow->result()
                    ->uid('THE ID')
                    ->title('Item Title')
                    ->subtitle('Item Subtitle');

        $workflow->result()
                    ->uid('THE ID 2')
                    ->title('Item Title 2')
                    ->subtitle('Item Subtitle 2');

        $expected = [
            'items' => [
                [
                    'subtitle'     => 'Item Subtitle 2',
                    'title'        => 'Item Title 2',
                    'uid'          => 'THE ID 2',
                    'valid'        => true,
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $workflow->filterResults('ID 2', 'uid')->output());
    }
}
