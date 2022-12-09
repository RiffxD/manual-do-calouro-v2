<?php

namespace App\Controller\Admin;

use App\Http\Request;
use App\Models\Aula as EntitySchedule;
use App\Utils\Database;
use App\Utils\Tools\Alert;
use App\Utils\Pagination;
use App\Utils\View;

class Schedule extends Page {

    /**
     * Método responsavel por obter a renderização dos items de usuarios para página
     * @param \App\Http\Request $request
     * @param \App\Utils\Pagination $obPagination
     * 
     * @return string
     */
    private static function getScheduleItems(Request $request, &$obPagination): string {
        // USUARIOS
        $itens = '';

        // QUANTIDADE TOTAL DE REGISTROS
        $quantidadeTotal = EntitySchedule::getSchedules(null, null, null, 'COUNT(*) AS qtd')->fetchObject()->qtd;

        // PAGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;

        // INSTANCIA DE PAGINAÇÃO
        $obPagination = new Pagination($quantidadeTotal, $paginaAtual, 10);

        // RESULTADOS DA PAGINA
        $results = EntitySchedule::getDscSchedules('id_aula ASC', $obPagination->getLimit());

        // RENDERIZA O ITEM
        while ($obShedule = $results->fetch(\PDO::FETCH_ASSOC)) {
            // VIEW De DEPOIMENTOSS
            $itens .= View::render('admin/modules/schedules/item',[
                'id'         => $obShedule['id_aula'],
                'semana'     => $obShedule['dsc_dia_semana'],
                'horario'    => $obShedule['hora_aula_inicio'],
                'sala'       => $obShedule['dsc_sala_aula'],
                'disciplina' => $obShedule['dsc_disciplina'],
                'professor'  => $obShedule['nom_usuario']
            ]);
        }

        // RETORNA OS DEPOIMENTOS
        return $itens;
    }

       /**
     * Método responsavel por renderizar a view de listagem de usuarios
     * @param \App\Http\Request
     * 
     * @return string
     */
    public static function getSchedules(Request $request): string {
        // CONTEUDO DA HOME
        $content = View::render('admin/modules/schedules/index', [
            'itens'      => self::getScheduleItems($request, $obPagination),
            'pagination' => parent::getPagination($request, $obPagination),
            'status'     => Alert::getStatus($request)
        ]);

        // RETORNA A PAGINA COMPLETA
        return parent::getPanel('Horarios > MDC', $content, 'schedules');
    }
    
    /**
     * Método responsavel por renderizar o formulario de cadastro de aula
     * @param \App\Http\Request $request
     * 
     * @return string
     */
    public static function getNewSchedule(Request $request): string {
        $obDatabase = new Database;

        $content = View::render('admin/modules/schedules/form', [
           'tittle'     => 'Cadastrar aula',
           'status'     => Alert::getStatus($request),
           'semana'     => self::getWeekDays($obDatabase),
           'horario'    => self::getSchedule($obDatabase),
           'sala'       => self::getRooms($obDatabase),
           'disciplina' => self::getSubjects($obDatabase),
           'professor'  => self::getTeachers($obDatabase)
        ]);

        return parent::getPanel('Cadastrar aula > MDC', $content, 'horario');
    }

    public static function getEditSchedule(Request $request, int $id) {
        
        $obSchedule = EntitySchedule::getScheduleById($id);

        
    }

    /**
     * Método responsável por renderizar as opções de dia da semana
     * @param \App\Utils\Database $obDatabase
     * 
     * @return string
     */
    private static function getWeekDays(Database $obDatabase): string {
        // DECLARAÇÃO DE VARIAVEIS
        $content = '';
        $diaSemana = $obDatabase->selectAll('dia_semana');

        // RENDERIZA AS OPÇÕES DA SEMANA
        for ($i = 0; $i < count($diaSemana); $i++) {
            $content .= self::getOption($diaSemana[$i], [
                'id_dia_semana',
                'dsc_dia_semana'
            ]);
        }
        // RETORNA O CONTEUDO
        return $content;
    }

    /**
     * Método 
     * @param \App\Utils\Database $obDatabase
     * 
     */
    private static function getSchedule(Database $obDatabase): string {
        $content = '';
        $horarioAula = $obDatabase->selectAll('horario_aula');

        // RENDERIZA AS OPÇÕES DE HORARIO
        for ($i = 0; $i < count($horarioAula); $i++) {
            $horario = array(
                'id_horario' => $horarioAula[$i]['id_horario_aula'],
                'inicio_fim' => $horarioAula[$i]['hora_aula_inicio'] . ' - ' . $horarioAula[$i]['hora_aula_fim']
            );

            $content .= self::getOption($horario, [
                'id_horario',
                'inicio_fim'
            ]);
        }
        return $content;
    }

    /**
     * 
     * @param \App\Utils\Database $obDatabase
     */
    private static function getRooms(Database $obDatabase): string {
        $content = '';
        $salaAula = $obDatabase->selectAll('sala_aula');

        // RENDERIZA AS OPÇÕES DA SEMANA
        for ($i = 0; $i < count($salaAula); $i++) {
            $content .= self::getOption($salaAula[$i], [
                'id_sala_aula',
                'dsc_sala_aula'
            ]);
        }
        return $content;
    }

    /**
     * 
     * @param \App\Utils\Database $obDatabase
     */
    private static function getSubjects(Database $obDatabase): string {
        $content = '';
        $disciplina = $obDatabase->selectAll('disciplina');

        // RENDERIZA AS OPÇÕES DAS DISCIPLINA
        for ($i = 0; $i < count($disciplina); $i++) {
            $content .= self::getOption($disciplina[$i], [
                'id_disciplina',
                'dsc_disciplina'
            ]);
        }
        return $content;
    }

    /**
     * 
     * @param \App\Utils\Database $obDatabase
     */
    private static function getTeachers(Database $obDatabase): string {
        $content = '';

        $sql = 'professor p JOIN servidor s ON (p.fk_servidor_fk_usuario_id_usuario = s.fk_usuario_id_usuario) JOIN usuario u ON (s.fk_usuario_id_usuario = u.id_usuario)';

        $professor = $obDatabase->selectAll($sql, 'id_usuario, nom_usuario');

        // RENDERIZA AS OPÇÕES DAS DISCIPLINA
        for ($i = 0; $i < count($professor); $i++) {
            $content .= self::getOption($professor[$i], [
                'id_usuario',
                'nom_usuario'
            ]);
        }
        return $content;
    }



    /**
     * 
     * @param array $array
     * @param array $keys
     * 
     * @return string
     */
    private static function getOption($array, $keys): string {
        return View::render('/admin/modules/schedules/option', [
            'id'    => $array[$keys[0]],
            'valor' => $array[$keys[1]]
        ]);
    }
}