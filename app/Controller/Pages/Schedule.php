<?php

namespace App\Controller\Pages;

use App\Http\Request;
use App\Models\Aula as EntitySchedule;
use App\Utils\Sanitize;
use App\Utils\View;

class Schedule extends Page {
    
    /**
     * Método responsável por retornar o contéudo (view) da página horario
     * @param \App\Http\Request
     * 
     * @return string
     */
    public static function getSchedule(Request $request): string {
        // QUERY PARAMS
        $queryParams = Sanitize::sanitizeForm($request->getQueryParams());

        $curso = $queryParams['curso'] ?? '';
        $modulo = $queryParams['modulo'] ?? '';

        // VERIFICA SE HOUVE EXISTE PARAMETROS PRA CONSULTA DE HORARIOS
        if (!empty($curso) and !empty($modulo)) {
            // OBTEM OS DADOS PARA TABELA
            $nome  = self::getCurso($curso);
            $table = self::getTable($curso, $modulo);

            $content = View::render('pages/schedule', [
                'curso'    => $nome,
                'modulo'   => $modulo,
                'horarios' => $table,
                'hidden'   => ''
            ]);
        // RENDENIZA A O HORARIO SEM TABELA    
        } else {
            $content = View::render('pages/schedule', [
                'horarios' => '',
                'curso'    => '',
                'modulo'   => '',
                'hidden'   => 'd-none'
            ]);
        }
        // RETORNA A VIEW DA PAGINA
        return parent::getPage('Horários', $content, 'schedule');
    }

    /**
     * Método responsável por retornar o nome do curso
     * @param  integer $curso
     * 
     * @return string
     */
    private static function getCurso(int $curso): string {
        // RETORNA O NOME DO CURSO
        $curso = EntitySchedule::getCursoById($curso);
        return $curso['dsc_curso'];
    }

    /**
     * Método responsável por retornar a view da tabela do horário
     * @param integer $curso
     * @param integer $modulo
     * 
     * @return string
     */
    public static function getTable(int $curso, int $modulo): string {
        // DECLARAÇÃO DE VARIAVEIS
        $count = 0;
        $content = '';

        $aulas = EntitySchedule::getScheduleClass($curso, $modulo);
        $horas = EntitySchedule::getScheduleTimes();

        // REDENIZA A TABELA
        for ($i = 0; $i < count($horas); $i++) {
            $content .= View::render('pages/components/schedule/row', [
                'hora_inicio' => $horas[$i]['hora_aula_inicio'],
                'hora_fim'    => $horas[$i]['hora_aula_fim'],
                'aulas'       => self::getRow($aulas, $count)
            ]);
        }
        // RETORNA O CONTEÚDO DA PÁGINA
        return $content;
    }
    
    /**
     * Método responsável por rendenizar a linha de items do horário
     * @param  array   $aulas
     * @param  integer $count
     * 
     * @return string
     */
    public static function getRow(array $aulas, int &$count): string {        
        $content = '';

        $double = [
            'a' => [
                'sala' => '-',
                'materia' => '-',
                'professor' => '-'
            ],
            'b' => [
                'sala' => '-',
                'materia' => '-',
                'professor' => '-'
            ]
        ];

        // Loop para cada aula
        for ($i = 0; $i < 6; $i++) {
            // VERIFICA SE É UMA AULA DE TURMA COMPLETA
            if ($aulas[$count]['grupo'] == 'C') {
                // VIEW DO HORÁRIO
                $content .= self::getItem($aulas[$count]);
            } else {
                if ($aulas[$count]['grupo'] == 'A') {
                    $double['a'] = [
                        'sala' => $aulas[$count]['sala'],
                        'materia' => $aulas[$count]['materia'],
                        'professor' => $aulas[$count]['professor'],
                    ];
                    if ($aulas[$count+1]['grupo'] == 'B') {
                        $double['b'] = [
                            'sala' => $aulas[$count+1]['sala'],
                            'materia' => $aulas[$count+1]['materia'],
                            'professor' => $aulas[$count+1]['professor'],
                        ];
                        $count++;
                    }
                } else {
                    $double['b'] = [
                        'sala' => $aulas[$count]['sala'],
                        'materia' => $aulas[$count]['materia'],
                        'professor' => $aulas[$count]['professor'],
                    ];
                }
                $content .= self::getDoubleItem($double);
            }
            $count++;
        }
        // RETORNA A VIEW DA LINHA
        return $content;
    }

    /**
     * Método responsável por criar cada item de aula da tabela
     * @param  array $class
     * 
     * @return string
     */ 
    private static function getItem(array $aula): string {
        // RETORNA A VIEW DA COLUNA
        return View::render('pages/components/schedule/item', [
            'sala' => $aula['sala'],
            'materia' => $aula['materia'],
            'professor' => $aula['professor']
        ]);
    }

    private static function getDoubleItem($double) {
        return View::render('pages/components/schedule/double', [
            'sala-a' => $double['a']['sala'],
            'materia-a' => $double['a']['materia'],
            'professor-a' => $double['a']['professor'],
            'sala-b' => $double['b']['sala'],
            'materia-b' => $double['b']['materia'],
            'professor-b' => $double['b']['professor']
        ]);
    }
}