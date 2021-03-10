<?php

namespace Action {

    use Actions\Context;
    use Actions\Response;
    use ErrorException;
    use Exception;
    use Models\PersonInitRegPriz10;
    use Models\PersonPriz01;
    use Models\RecruitOfficeR8012;
    use Structures\PersonCardReply;

    /**
     * Class ActionPersonsSearch to search persons
     * @package Actions
     */
    class ActionPersonsSearch extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         * @throws ErrorException
         */
        public function Execute($ctx)
        {
            switch ($ctx->method) {
                case "GET":
                    $this->ExecuteGET($ctx);
                    break;
                default:
                    (new Response())->AddError(405, "incorrect_method")->AddContext($ctx)->Reply();
            }

        }

        /**
         * GET request actions
         * @param Context $ctx Request context
         * @throws ErrorException
         */
        private function ExecuteGET($ctx)
        {
            // Filters
            $filterName = empty($ctx->args["name"]) ? "" : $ctx->pg->escape($ctx->args["name"]);
            $filterBirthYear = empty($ctx->args["birthYear"]) ? "" : $ctx->pg->escape($ctx->args["birthYear"]);
            $filterRecruitOffice = empty($ctx->args["recruitOffice"]) ? "" : $ctx->pg->escape($ctx->args["recruitOffice"]);
            $filterPersonalID = empty($ctx->args["personalID"]) ? "" : $ctx->pg->escape($ctx->args["personalID"]);
            $filterLocalCommand = empty($ctx->args["localCommand"]) ? "" : $ctx->pg->escape($ctx->args["localCommand"]);

            // results limit per each results type
            $limit = empty($ctx->args["limit"]) ? 10000 : $ctx->pg->escape($ctx->args["limit"]);

            // Generating query for recruit office
            $sqlRecruitOffice = "AND rec_office_id ILIKE '$filterRecruitOffice'";
            if (empty($filterRecruitOffice)) $sqlRecruitOffice = "";

            // Generating query for local command
            $sqlLocalCommandQuery = "AND extra ILIKE '%\"localCommand\":\"%$filterLocalCommand%\"%'";
            if (empty($filterLocalCommand)) $sqlLocalCommandQuery = "";

            // SQL query template
            $sql = "
                SELECT *
                FROM (
                         SELECT person.p001::text                                       AS id,
                                person.p005 || ' ' || person.p006 || ' ' || person.p007 AS person_name,
                                person.k101::text                                       AS person_birth_year,
                                recruiter.nom_li                                        AS personal_id,
                                rec_office.p01                                          AS rec_office_name,
                                rec_office.p00                                          AS rec_office_id,
                                init_reg.p100::text                                     AS extra
                         FROM priz01 as person
                                  JOIN priz10 as init_reg
                                       ON person.p001 = init_reg.p001
                                  JOIN r8012 as rec_office
                                       ON substr(person.pnom, 0, 9) = rec_office.p00
                                  JOIN gsp01_ur as recruiter
                                       ON person.pnom = recruiter.pnom
                     ) AS card
                WHERE 
                      person_name ILIKE '%$filterName%'
                      AND person_birth_year ILIKE '%$filterBirthYear%'
                      $sqlRecruitOffice                
                      AND personal_id ILIKE '%$filterPersonalID%'
                      $sqlLocalCommandQuery
                ORDER BY person_name
                LIMIT $limit;
            ";

            // SQL query
            $cards = $ctx->pg->query($sql);

            // Aliases for more short names
            function getRecruitOfficeAlias($id)
            {
                $aliases = [
                    ["id" => "08489495", "name" => "Октябрьский и Железнодорожный"],
                    ["id" => "08489526", "name" => "Первомайский и Ленинский"],
                    ["id" => "08489992", "name" => "г.Кузнецк, Кузнецкий и Сосновоборский"],
                    ["id" => "08489561", "name" => "Башмаковский и Пачелмский"],
                    ["id" => "08489696", "name" => "Белинский и Тамалинский"],
                    ["id" => "08489650", "name" => "Бессоновский и Мокшанский"],
                    ["id" => "08489733", "name" => "Городищенский и Никольский"],
                    ["id" => "08489673", "name" => "г. Заречный"],
                    ["id" => "08489785", "name" => "Земетчинский и Вадинский"],
                    ["id" => "08489851", "name" => "Каменский"],
                    ["id" => "08489940", "name" => "Колышлейский и М.Сердобинский"],
                    ["id" => "08490015", "name" => "Лунинский и Иссинский"],
                    ["id" => "08490050", "name" => "Неверкинский и Камешкирский"],
                    ["id" => "08490133", "name" => "Н.Ломовский и Наровчатский Спасский"],
                    ["id" => "08490328", "name" => "Пензенский"],
                    ["id" => "08490334", "name" => "Сердобский и Бековский"],
                    ["id" => "08490392", "name" => "Шемышейский и Лопатинский"],
                ];
                for ($i = 0; $i < count($aliases); $i++) {
                    if ($aliases[$i]["id"] == $id) return $aliases[$i]["name"];
                }
                return "";
            }

            // Responses repack
            $result = [];
            foreach ($cards as $card) {
                $extra = json_decode($card["extra"], true);
                // If extra is NULL create new with empty fields
                if ($extra === null) $extra = ["comment" => $card["extra"]];
                // If some fields doesn't exists - create them
                if (empty($extra["localCommand"])) $extra["localCommand"] = "";
                if (empty($extra["comment"])) $extra["comment"] = "";

                $newCard = [
                    "id" => $card["id"],
                    "name" => $card["person_name"],
                    "birthYear" => $card["person_birth_year"],
                    "personalID" => $card["personal_id"],
                    "recruitOfficeName" => getRecruitOfficeAlias($card["rec_office_id"]),
                    "recruitOfficeID" => $card["rec_office_id"],
                    "extra" => $extra,
                ];
                $result[] = $newCard;
            }

            (new Response($result))->Reply();
        }
    }

}
