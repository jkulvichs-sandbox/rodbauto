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
     * Class ActionSummaryTotal returns total info about deliveries
     * @package Actions
     */
    class ActionSummaryTotal extends Action
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
         * GET total info
         * @param Context $ctx Request context
         * @throws ErrorException
         */
        private function ExecuteGET($ctx)
        {
            // Query to get delivered date with count for every recruit office
            $deliveries = $ctx->pg->query("
                select r8012                       as roid,                       
                       extract(epoch from p105_g2) as delivered,
                       count(*)                    as count
                from gsp01_ur
                where p105_g2 is not null
                group by r8012, p105_g2;
            ");

            // Summary rows for every recruit offices
            $rosum = [];
            foreach ($deliveries as $d) {

                $roid = $d["roid"];

                if (empty($rosum[$roid])) {
                    $rosum[$roid] = [
                        "recruitOffice" => $ctx->sqlite->getRecruitOfficeAlias($roid),
                        "registered" => $ctx->sqlite->getRecruitOfficeRegistered($roid),
                        "stored" => $this->getPeopleCount($ctx, $roid),
                        "deliveries" => [],
                        "taskPlan" => $ctx->sqlite->getRecruitOfficeTask($roid),
                    ];
                }
                $rosum[$roid]["deliveries"][] = [
                    "date" => (int)$d["delivered"],
                    "count" => (int)$d["count"],
                ];
            }

            (new Response($rosum))->Reply();
        }

        /**
         * Get total count of people for recruit office
         * @param Context $ctx Request context
         * @param string $roid ID of Recruit Office
         * @throws ErrorException
         */
        private function getPeopleCount($ctx, $roid)
        {
            // Fetch all IDs from main DB
            $mainIDs = array_map(
                function ($row) {
                    return $row["id"];
                },
                $ctx->pg->query("
                    SELECT *
                    FROM (
                             SELECT person.p001::text AS id,
                                    rec_office.p00    AS rec_office_id
                             FROM priz01 as person
                                      JOIN priz10 as init_reg
                                           ON person.p001 = init_reg.p001
                                      JOIN r8012 as rec_office
                                           ON substr(person.pnom, 0, 9) = rec_office.p00
                         ) AS card
                    WHERE rec_office_id ILIKE '$roid';                
                ")
            );
            // Fetch rows' count from local DB with filters
            $sMainIDs = join(", ", $mainIDs);
            return $ctx->sqlite->query("
                SELECT COUNT(*) AS count FROM people WHERE p001 IS NOT NULL AND p001 IN ($sMainIDs)
            ")[0]["count"];
        }
    }

}

/* Response structure
{
    rows: [
        {
            recruitOffice: "08489495", // RO ID
            registered: 600, // Manual written data
            loaded: 300, // In DB
            task: 700, // The task complete at value
            days: [
                {
                    unix: "1621281600", // unix time in seconds
                    count: 5
                },
            ]
        },
    ]
}
 */
