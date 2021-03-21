curl -i -d '{"function":"0", "code":"103"}' -H "Content-Type: application/json"  http://noubty/index.php
echo "\n"
curl -d '{"function":"0", "code":"112233445566778899"}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
curl -d '{"function":"8", "event_id":1}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
curl -d '{"function":"4", "event_id":1}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
curl -d '{"function":"21", "service_id":1, "additional_information":"nothing"}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
curl -d '{"function":"40", "service_id":6}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
curl -d '{"function":"20", "service_id":10}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
curl -d '{"function":"9", "service_id":5}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
curl -d '{"function":"137", "service_id":17}' -H "Content-Type: application/json" -X POST http://noubty
echo "\n"
