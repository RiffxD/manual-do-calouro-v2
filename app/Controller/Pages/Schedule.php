<?php

namespace App\Controller\Pages;

use App\Utils\View;
use App\Models\Entity\Schedule as EntitySchedule;

class Schedule extends Page {
    
    /**
     * Metodo responsavel por retornar o contéudo (view) da pagina horario
     * @param \App\Http\Request
     * @return string 
     */
    public static function getSchedule($request) {
        // QUERY PARAMS
        $queryParams = $request->getQueryParams();

        $curso = $queryParams['curso'] ?? '';
        $modulo = $queryParams['modulo'] ?? '';

        // VERIFICA SE HOUVE EXISTE PARAMETROS PRA CONSULTA DE HORARIOS
        if (!empty($curso) and !empty($modulo)) {
            // OBTEM OS DADOS PARA TABELA
            $tableSchedule = self::getTable($curso, $modulo);

            $content = View::render('pages/schedule', [
                'horarios' => $tableSchedule
            ]);
        // RENDENIZA A O HORARIO SEM TABELA    
        } else {
            $content = View::render('pages/schedule', [
                'horarios' => ''
            ]);
        }
        // RETORNA A VIEW DA PAGINA
        return parent::getHeader('Horários', $content, 'schedule');
    }

    /**
     * Metodo responsavel por retornar a view da tabela do horario
     * 
     */
    public static function getTable($curso, $modulo) {
        $obSchedule = EntitySchedule::getSchedule($curso, $modulo);
        $obTime     = EntitySchedule::getScheduleTime();

        $count = 0;
        $content = '';
        
        for ($i = 0; $i < count($obTime); $i++) {
            $content .= View::render('pages/schedule/row', [
                'hora_inicio' => $obTime[$i]['hora_aula_inicio'],
                'hora_fim'    => $obTime[$i]['hora_aula_fim'],
                'aulas'       => self::getRow($obSchedule, $count)
            ]);
        }
        // RETORNA O CONTEÚDO DA PÁGINA
        return $content;
    }
    
    /**
     * Metodo responsavel por rendenizar a linha de items do horario
     * @param array $obSchedule
     * @param integer $count
     */
    public static function getRow($obSchedule, &$count) {        
        $content = '';
        
        // Loop para cada aula
        for ($i = 0; $i < 6; $i++) { 
            // VIEW DO HORÁRIO
            $content .= self::getItem($obSchedule[$count]);
            $count++;
        }
        return $content;
    }

    /**
     * Metodo responsavel
     * @param array $class
     * @return string
     */ 
    public static function getItem($class) {
        // VIEW DA COLUNA
        return View::render('pages/schedule/item', [
            'sala' => $class['sala'],
            'materia' => $class['materia'],
            'professor' => $class['professor']
        ]);
    }
}