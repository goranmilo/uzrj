<?php

return [

    'builder' => [
        'actions' => [
            'clone' => [
                'label' => 'Dupliraj',
            ],

            'add' => [
                'label' => 'Dodaj u :label',
                'modal' => [
                    'heading' => 'Dodaj u :label',
                    'actions' => [
                        'add' => [
                            'label' => 'Dodaj',
                        ],
                    ],
                ],
            ],

            'add_between' => [
                'label' => 'Umetni između blokova',
                'modal' => [
                    'heading' => 'Dodaj u :label',
                    'actions' => [
                        'add' => [
                            'label' => 'Dodaj',
                        ],
                    ],
                ],
            ],

            'delete' => [
                'label' => 'Obriši',
            ],

            'edit' => [
                'label' => 'Izmeni',
                'modal' => [
                    'heading' => 'Izmena bloka',
                    'actions' => [
                        'save' => [
                            'label' => 'Sačuvaj izmene',
                        ],
                    ],
                ],
            ],

            'reorder' => [
                'label' => 'Premesti',
            ],

            'move_down' => [
                'label' => 'Pomeri dole',
            ],

            'move_up' => [
                'label' => 'Pomeri gore',
            ],

            'collapse' => [
                'label' => 'Skupi',
            ],

            'expand' => [
                'label' => 'Proširi',
            ],

            'collapse_all' => [
                'label' => 'Skupi sve',
            ],

            'expand_all' => [
                'label' => 'Proširi sve',
            ],
        ],
    ],

    'checkbox_list' => [
        'actions' => [
            'deselect_all' => [
                'label' => 'Poništi izbor',
            ],

            'select_all' => [
                'label' => 'Izaberi sve',
            ],
        ],
    ],

    'file_upload' => [
        'editor' => [
            'actions' => [
                'cancel' => [
                    'label' => 'Otkaži',
                ],

                'drag_crop' => [
                    'label' => 'Režim „isecanje“',
                ],

                'drag_move' => [
                    'label' => 'Režim „pomeranje“',
                ],

                'flip_horizontal' => [
                    'label' => 'Preokreni horizontalno',
                ],

                'flip_vertical' => [
                    'label' => 'Preokreni vertikalno',
                ],

                'move_down' => [
                    'label' => 'Pomeri sliku dole',
                ],

                'move_left' => [
                    'label' => 'Pomeri sliku levo',
                ],

                'move_right' => [
                    'label' => 'Pomeri sliku desno',
                ],

                'move_up' => [
                    'label' => 'Pomeri sliku gore',
                ],

                'reset' => [
                    'label' => 'Vrati na početno',
                ],

                'rotate_left' => [
                    'label' => 'Rotiraj ulevo',
                ],

                'rotate_right' => [
                    'label' => 'Rotiraj udesno',
                ],

                'set_aspect_ratio' => [
                    'label' => 'Odnos stranica :ratio',
                ],

                'save' => [
                    'label' => 'Sačuvaj',
                ],

                'zoom_100' => [
                    'label' => 'Uvećanje 100%',
                ],

                'zoom_in' => [
                    'label' => 'Uvećaj',
                ],

                'zoom_out' => [
                    'label' => 'Umanji',
                ],
            ],

            'fields' => [
                'height' => [
                    'label' => 'Visina',
                    'unit' => 'px',
                ],

                'rotation' => [
                    'label' => 'Rotacija',
                    'unit' => 'step.',
                ],

                'width' => [
                    'label' => 'Širina',
                    'unit' => 'px',
                ],

                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],

                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],
            ],

            'aspect_ratios' => [
                'label' => 'Odnos stranica',
                'no_fixed' => [
                    'label' => 'Slobodno',
                ],
            ],

            'svg' => [
                'messages' => [
                    'confirmation' => "Izmena SVG fajlova se ne preporučuje jer može smanjiti kvalitet pri skaliranju.
 Da li ste sigurni da želite da nastavite?",
                    'disabled' => 'Izmena SVG fajlova je isključena jer može smanjiti kvalitet pri skaliranju.',
                ],
            ],
        ],
    ],

    'key_value' => [
        'actions' => [
            'add' => [
                'label' => 'Dodaj red',
            ],

            'delete' => [
                'label' => 'Obriši red',
            ],

            'reorder' => [
                'label' => 'Premesti red',
            ],
        ],

        'fields' => [
            'key' => [
                'label' => 'Ključ',
            ],

            'value' => [
                'label' => 'Vrednost',
            ],
        ],
    ],

    'markdown_editor' => [
        'toolbar_buttons' => [
            'attach_files' => 'Priloži fajlove',
            'blockquote' => 'Citat',
            'bold' => 'Podebljano',
            'bullet_list' => 'Lista sa tačkama',
            'code_block' => 'Blok koda',
            'heading' => 'Naslov',
            'italic' => 'Kurziv',
            'link' => 'Link',
            'ordered_list' => 'Numerisana lista',
            'redo' => 'Ponovi',
            'strike' => 'Precrtano',
            'table' => 'Tabela',
            'undo' => 'Poništi',
        ],
    ],

    'radio' => [
        'boolean' => [
            'true' => 'Da',
            'false' => 'Ne',
        ],
    ],

    'repeater' => [
        'actions' => [
            'add' => [
                'label' => 'Dodaj u :label',
            ],

            'add_between' => [
                'label' => 'Umetni između',
            ],

            'delete' => [
                'label' => 'Obriši',
            ],

            'clone' => [
                'label' => 'Dupliraj',
            ],

            'reorder' => [
                'label' => 'Premesti',
            ],

            'move_down' => [
                'label' => 'Pomeri dole',
            ],

            'move_up' => [
                'label' => 'Pomeri gore',
            ],

            'collapse' => [
                'label' => 'Skupi',
            ],

            'expand' => [
                'label' => 'Proširi',
            ],

            'collapse_all' => [
                'label' => 'Skupi sve',
            ],

            'expand_all' => [
                'label' => 'Proširi sve',
            ],
        ],
    ],

    'rich_editor' => [
        'dialogs' => [
            'link' => [
                'actions' => [
                    'link' => 'Poveži',
                    'unlink' => 'Ukloni link',
                ],

                'label' => 'URL',
                'placeholder' => 'Unesite URL',
            ],
        ],

        'toolbar_buttons' => [
            'attach_files' => 'Priloži fajlove',
            'blockquote' => 'Citat',
            'bold' => 'Podebljano',
            'bullet_list' => 'Lista sa tačkama',
            'code_block' => 'Blok koda',
            'h1' => 'Naslov',
            'h2' => 'Podnaslov',
            'h3' => 'Manji podnaslov',
            'italic' => 'Kurziv',
            'link' => 'Link',
            'ordered_list' => 'Numerisana lista',
            'redo' => 'Ponovi',
            'strike' => 'Precrtano',
            'underline' => 'Podvučeno',
            'undo' => 'Poništi',
        ],
    ],

    'select' => [
        'actions' => [
            'create_option' => [
                'label' => 'Dodaj',
                'modal' => [
                    'heading' => 'Dodavanje',
                    'actions' => [
                        'create' => [
                            'label' => 'Sačuvaj',
                        ],

                        'create_another' => [
                            'label' => 'Sačuvaj i dodaj novi',
                        ],
                    ],
                ],
            ],

            'edit_option' => [
                'label' => 'Izmeni',
                'modal' => [
                    'heading' => 'Izmena',
                    'actions' => [
                        'save' => [
                            'label' => 'Sačuvaj',
                        ],
                    ],
                ],
            ],
        ],

        'boolean' => [
            'true' => 'Da',
            'false' => 'Ne',
        ],

        'loading_message' => 'Učitavanje...',
        'max_items_message' => 'Može se izabrati najviše :count.',
        'no_search_results_message' => 'Nijedna opcija ne odgovara pretrazi.',
        'placeholder' => 'Izaberite',
        'searching_message' => 'Pretraga...',
        'search_prompt' => 'Počnite da kucate za pretragu...',
    ],

    'tags_input' => [
        'placeholder' => 'Nova oznaka',
    ],

    'text_input' => [
        'actions' => [
            'hide_password' => [
                'label' => 'Sakrij lozinku',
            ],

            'show_password' => [
                'label' => 'Prikaži lozinku',
            ],
        ],
    ],

    'toggle_buttons' => [
        'boolean' => [
            'true' => 'Da',
            'false' => 'Ne',
        ],
    ],

    'wizard' => [
        'actions' => [
            'previous_step' => [
                'label' => 'Nazad',
            ],

            'next_step' => [
                'label' => 'Dalje',
            ],
        ],
    ],

];
