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
     * Class ActionRecruitOfficesList to manipulate with list of recruit offices
     * @package Actions
     */
    class ActionRecruitOfficesList extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         */
        public function Execute($ctx)
        {
            //TODO: Switch sets based on region in query
            switch ($ctx->method) {
                case "GET":
                    (new Response([
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
                    ]))->Reply();
                    break;
                default:
                    (new Response())->AddError(405, "incorrect_method")->AddContext($ctx)->Reply();
            }

        }
    }

}
