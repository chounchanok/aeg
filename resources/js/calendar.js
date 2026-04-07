import { Calendar } from "@fullcalendar/core";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";

(function () {
    if ($("#calendar1").length) {
        if ($("#calendar-events").length) {
            new Draggable($("#calendar-events")[0], {
                itemSelector: ".event",
                eventData: function (eventEl) {
                    return {
                        title: $(eventEl).find(".event__title").html(),
                        duration: {
                            days: parseInt(
                                $(eventEl).find(".event__days").text()
                            ),
                        },
                    };
                },
            });
        }

        let calendar = new Calendar($("#calendar1")[0], {
            plugins: [
                interactionPlugin,
                dayGridPlugin,
                timeGridPlugin,
                listPlugin,
            ],
            droppable: true,
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
            },
            initialDate: new Date().toISOString().slice(0, 10), // ตั้งค่าให้เป็นวันที่ปัจจุบัน
            navLinks: true,
            editable: true,
            dayMaxEvents: true,
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: "/get-calendar-events",
                    type: "GET",
                    dataType: "json",
                    data: {
                        month: fetchInfo.start.getMonth() + 1, // เดือนปัจจุบัน
                        year: fetchInfo.start.getFullYear(), // ปีปัจจุบัน
                    },
                    success: function(response) {
                        successCallback(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching events:", error);
                        failureCallback(error);
                    },
                });
            },
            drop: function (info) {
                if ($("#checkbox-events").length && $("#checkbox-events")[0].checked) {
                    $(info.draggedEl).parent().remove();

                    if ($("#calendar-events").children().length == 1) {
                        $("#calendar-no-events").removeClass("hidden");
                    }
                }
            },
        });

        calendar.render();

    }
})();
