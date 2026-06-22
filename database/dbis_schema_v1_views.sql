-- View for Daily Sessions (Base View)
CREATE VIEW view_live_data_daily_sessions AS
SELECT
    ds.session_date AS week_date,
    ds.client_id,
    ANY_VALUE(ds.instructor_id) AS instructor_id,
    ANY_VALUE(ds.supervisor_id) AS supervisor_id,
    ANY_VALUE(CONCAT(
        IF(
            ds.instructor_comments IS NOT NULL
            AND ds.instructor_comments != '',
            CONCAT('instructor: ', ds.instructor_comments),
            ''
        ),
        IF(
            ds.supervisor_comments IS NOT NULL
            AND ds.supervisor_comments != '',
            CONCAT('
supervisor: ', ds.supervisor_comments),
            ''
        ),
        IF(
            ds.comments IS NOT NULL
            AND ds.comments != '',
            CONCAT('
Program Director: ', ds.comments),
            ''
        )
    )) AS comments,
    GROUP_CONCAT(ds.session_rating SEPARATOR ', ') AS session_quality_rating,
    1 AS status
FROM
    daily_sessions ds
WHERE
    ds.status NOT IN (1, 2)
GROUP BY
    ds.client_id,
    ds.session_date;

-- View for Session Hours
CREATE VIEW view_live_data_session_hours AS
SELECT
    session_date AS week_date,
    client_id,
    ROUND(
        SUM(
            TIMESTAMPDIFF(SECOND, start_time, end_time) / 3600
        ),
        2
    ) AS hours
FROM
    daily_sessions_teaching_duration
WHERE
    end_time IS NOT NULL
GROUP BY
    client_id,
    session_date;

CREATE VIEW view_live_data_session_hours_by_session AS
SELECT
    session_id,
    session_date,
    client_id,
    ROUND(
        SUM(
            TIMESTAMPDIFF(SECOND, start_time, end_time) / 3600
        ),
        2
    ) AS hours
FROM
    daily_sessions_teaching_duration
WHERE
    end_time IS NOT NULL
GROUP BY
    session_id,
    client_id,
    session_date;

-- View for Problem Behavior Duration and Frequency
CREATE VIEW view_live_data_pb_duration AS
SELECT
    session_date AS week_date,
    client_id,
    SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))) AS total_duration_of_problem_behavior,
    COUNT(*) AS frequency_of_problem_behavior,
    ROUND(
        SUM(
            TIMESTAMPDIFF(SECOND, start_time, end_time) / 3600
        ),
        2
    ) AS hours
FROM
    daily_sessions_pb_duration
WHERE
    end_time IS NOT NULL
GROUP BY
    client_id,
    session_date;

CREATE VIEW view_live_data_pb_duration_by_session AS
SELECT
    session_id,
    session_date,
    client_id,
    SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))) AS total_duration_of_problem_behavior,
    COUNT(*) AS frequency_of_problem_behavior,
    ROUND(
        SUM(
            TIMESTAMPDIFF(SECOND, start_time, end_time) / 3600
        ),
        2
    ) AS hours
FROM
    daily_sessions_pb_duration
WHERE
    end_time IS NOT NULL
GROUP BY
    session_id,
    client_id,
    session_date;

-- View for Mands Duration
CREATE VIEW view_live_data_mands_duration_by_date AS
SELECT
    session_date AS week_date,
    client_id,
    SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))) AS total_duration_of_mands,
    COUNT(*) AS frequency_of_mands,
    ROUND(
        SUM(
            TIMESTAMPDIFF(SECOND, start_time, end_time) / 3600
        ),
        2
    ) AS hours
FROM
    daily_sessions_mands_duration
WHERE
    end_time IS NOT NULL
GROUP BY
    client_id,
    session_date;

CREATE VIEW view_live_data_mands_duration_by_session AS
SELECT
    session_id,
    session_date,
    client_id,
    SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))) AS total_duration_of_mands,
    COUNT(*) AS frequency_of_mands,
    ROUND(
        SUM(
            TIMESTAMPDIFF(SECOND, start_time, end_time) / 3600
        ),
        2
    ) AS hours
FROM
    daily_sessions_mands_duration
WHERE
    end_time IS NOT NULL
GROUP BY
    session_id,
    client_id,
    session_date;

-- View for Skills Retained
CREATE VIEW view_live_data_skills_retained AS
SELECT
    session_date AS week_date,
    client_id,
    COUNT(*) AS skills_retained
FROM
    client_program_targets_retained
GROUP BY
    client_id,
    session_date;

-- View for DOI (Duration of Intervention)
CREATE VIEW view_live_data_doi AS
SELECT
    session_date AS week_date,
    client_id,
    SUM(doi_value) AS doi
FROM
    client_program_targets_doi
GROUP BY
    client_id,
    session_date;

-- View for Program Changes
CREATE VIEW view_live_data_program_changes AS
SELECT
    session_date AS week_date,
    client_id,
    COUNT(*) AS program_change_made
FROM
    client_program_change
GROUP BY
    client_id,
    session_date;

-- View for Mands (Total and Variety)
CREATE VIEW view_live_data_mands AS
SELECT
    session_date AS week_date,
    client_id,
    COUNT(*) AS total_mands,
    COUNT(DISTINCT reinforcer_input) AS variety_of_mands
FROM
    mands_session_data
GROUP BY
    client_id,
    session_date;

-- Final Combined View
CREATE VIEW view_live_data_combined AS
SELECT
    ds.week_date,
    ds.client_id,
    ds.instructor_id,
    ds.supervisor_id,
    ds.comments,
    ds.session_quality_rating,
    ds.status,
    sh.hours,
    pb.total_duration_of_problem_behavior,
    pb.frequency_of_problem_behavior,
    sr.skills_retained,
    doi.doi,
    pc.program_change_made,
    mands.total_mands,
    mands.variety_of_mands
FROM
    view_live_data_daily_sessions ds
    LEFT JOIN view_live_data_session_hours sh ON ds.client_id = sh.client_id
    AND ds.week_date = sh.week_date
    LEFT JOIN view_live_data_pb_duration pb ON ds.client_id = pb.client_id
    AND ds.week_date = pb.week_date
    LEFT JOIN view_live_data_skills_retained sr ON ds.client_id = sr.client_id
    AND ds.week_date = sr.week_date
    LEFT JOIN view_live_data_doi doi ON ds.client_id = doi.client_id
    AND ds.week_date = doi.week_date
    LEFT JOIN view_live_data_program_changes pc ON ds.client_id = pc.client_id
    AND ds.week_date = pc.week_date
    LEFT JOIN view_live_data_mands mands ON ds.client_id = mands.client_id
    AND ds.week_date = mands.week_date;

/**view_daily_data_combined**/
CREATE
OR REPLACE VIEW `view_daily_data_combined` AS
SELECT
    `id`,
    `week_date`,
    `client_id`,
    `instructor_id`,
    `supervisor_id`,
    `hours`,
    `skills_retained`,
    `doi`,
    `total_mands`,
    `variety_of_mands`,
    `frequency_of_problem_behavior`,
    `total_duration_of_problem_behavior`,
    `session_quality_rating`,
    `program_change_made`,
    `comments`,
    `status`,
    'manual' AS `data_source`
FROM
    `daily_session_manual`
UNION
ALL
SELECT
    NULL AS `id`,
    `week_date`,
    `client_id`,
    `instructor_id`,
    `supervisor_id`,
    `hours`,
    `skills_retained`,
    `doi`,
    `total_mands`,
    `variety_of_mands`,
    `frequency_of_problem_behavior`,
    `total_duration_of_problem_behavior`,
    `session_quality_rating`,
    `program_change_made`,
    `comments`,
    `status`,
    'live' AS `data_source`
FROM
    `view_live_data_combined`;

/* view_daily_data */
CREATE
OR REPLACE VIEW `view_daily_data` AS
SELECT
    `cs`.`client_id`,
    SUM(`cs`.`hours`) AS `hours`,
    SUM(`cs`.`skills_retained`) AS `skills_retained`,
    SUM(`cs`.`doi`) AS `doi`,
    `cs`.`week_date` - INTERVAL (DAYOFWEEK(`cs`.`week_date`) - `s`.`value` + 6) MOD 7 DAY AS `week_start_date`,
    `cs`.`week_date` - INTERVAL (DAYOFWEEK(`cs`.`week_date`) - `s`.`value` + 6) MOD 7 DAY + INTERVAL 6 DAY AS `week_end_date`
FROM
    `view_daily_data_combined` `cs`
    LEFT JOIN `settings` `s` ON (`s`.`key` = 'weekStartDay')
WHERE
    `cs`.`status` = 1
    AND NOT EXISTS (
        SELECT
            1
        FROM
            `view_daily_data_combined` `dup`
        WHERE
            `dup`.`client_id` = `cs`.`client_id`
            AND `dup`.`week_date` = `cs`.`week_date`
            AND `dup`.`data_source` = 'live'
            AND `cs`.`data_source` = 'manual'
    )
GROUP BY
    `cs`.`client_id`,
    `cs`.`week_date` - INTERVAL (DAYOFWEEK(`cs`.`week_date`) - `s`.`value` + 6) MOD 7 DAY,
    `cs`.`week_date` - INTERVAL (DAYOFWEEK(`cs`.`week_date`) - `s`.`value` + 6) MOD 7 DAY + INTERVAL 6 DAY;

/*view_daily_nosession*/
CREATE VIEW view_daily_nosession AS
SELECT
    cs.client_id,
    DATE_SUB(
        cs.week_date,
        INTERVAL ((DAYOFWEEK(cs.week_date) - s.value + 6) % 7) DAY
    ) AS week_start_date,
    DATE_ADD(
        DATE_SUB(
            cs.week_date,
            INTERVAL ((DAYOFWEEK(cs.week_date) - s.value + 6) % 7) DAY
        ),
        INTERVAL 6 DAY
    ) AS week_end_date
FROM
    view_daily_data_combined cs
    LEFT JOIN settings s ON s.`key` = 'weekStartDay'
GROUP BY
    cs.client_id,
    week_start_date,
    week_end_date
having
    (
        sum(
            (
                case
                    when (cs.status = 1) then 1
                    else 0
                end
            )
        ) = 0
    );

/* view_weekly_data */
CREATE VIEW view_weekly_data AS
SELECT
    csw.client_id,
    csw.hours,
    csw.skills_retained,
    csw.doi,
    csw.week_date,
    DATE_SUB(
        csw.week_date,
        INTERVAL ((DAYOFWEEK(csw.week_date) - s.value + 6) % 7) DAY
    ) AS week_start_date,
    DATE_ADD(
        DATE_SUB(
            week_date,
            INTERVAL ((DAYOFWEEK(csw.week_date) - s.value + 6) % 7) DAY
        ),
        INTERVAL 6 DAY
    ) AS week_end_date
FROM
    daily_session_manual_weekly csw
    LEFT JOIN settings s ON s.`key` = 'weekStartDay'
WHERE
    csw.status = 1;

/*view_weekly_nosession*/
CREATE VIEW view_weekly_nosession AS
SELECT
    csw.client_id,
    DATE_SUB(
        csw.week_date,
        INTERVAL ((DAYOFWEEK(csw.week_date) - s.value + 6) % 7) DAY
    ) AS week_start_date,
    DATE_ADD(
        DATE_SUB(
            week_date,
            INTERVAL ((DAYOFWEEK(csw.week_date) - s.value + 6) % 7) DAY
        ),
        INTERVAL 6 DAY
    ) AS week_end_date
FROM
    daily_session_manual_weekly csw
    LEFT JOIN settings s ON s.`key` = 'weekStartDay'
WHERE
    csw.status = 0;

/*view_daily_and_weekly_combined_data*/
CREATE VIEW view_daily_and_weekly_combined_data AS
select
    `a`.`client_id` AS `client_id`,
    `a`.`hours` AS `hours`,
    `a`.`skills_retained` AS `skills_retained`,
    `a`.`doi` AS `doi`,
    `a`.`week_end_date` AS `week_date`
from
    `view_daily_data` `a`
union
all
select
    `b`.`client_id` AS `client_id`,
    `b`.`hours` AS `hours`,
    `b`.`skills_retained` AS `skills_retained`,
    `b`.`doi` AS `doi`,
    `b`.`week_end_date` AS `week_date`
from
    `view_weekly_data` `b`
where
    (
        not(
            exists(
                select
                    1
                from
                    `view_daily_data` `a`
                where
                    (
                        (`a`.`client_id` = `b`.`client_id`)
                        and (`a`.`week_end_date` = `b`.`week_end_date`)
                    )
            )
        )
    );

/*view_daily_and_weekly_combined_nosession*/
CREATE VIEW view_daily_and_weekly_combined_nosession AS
select
    `a`.`client_id` AS `client_id`,
    `a`.`week_end_date` AS `week_date`
from
    `view_daily_nosession` `a`
union
all
select
    `b`.`client_id` AS `client_id`,
    `b`.`week_end_date` AS `week_date`
from
    `view_weekly_nosession` `b`
where
    (
        not(
            exists(
                select
                    1
                from
                    `view_daily_nosession` `a`
                where
                    (
                        (`a`.`client_id` = `b`.`client_id`)
                        and (`a`.`week_end_date` = `b`.`week_end_date`)
                    )
            )
        )
    );

/*view_cumulative_graph_data*/
CREATE VIEW view_cumulative_graph_data AS
select
    `view_daily_and_weekly_combined_data`.`client_id` AS `client_id`,
    `view_daily_and_weekly_combined_data`.`week_date` AS `week_date`,
    `view_daily_and_weekly_combined_data`.`hours` AS `hours`,
    `view_daily_and_weekly_combined_data`.`skills_retained` AS `skills_retained`,
    `view_daily_and_weekly_combined_data`.`doi` AS `doi`,
    1 AS `status`
from
    `view_daily_and_weekly_combined_data`
union
all
select
    `view_daily_and_weekly_combined_nosession`.`client_id` AS `client_id`,
    `view_daily_and_weekly_combined_nosession`.`week_date` AS `week_date`,
    NULL AS `hours`,
    NULL AS `skills_retained`,
    NULL AS `doi`,
    0 AS `status`
from
    `view_daily_and_weekly_combined_nosession`
order by
    `client_id`,
    `week_date`;

/*view_clients_skills_monthly_rate*/
CREATE VIEW view_clients_skills_monthly_rate AS
SELECT
    client_id,
    MAX(week_date) AS sortDate,
    MAX(DATE_FORMAT(week_date, '%Y-%m')) AS months,
    MAX(DATE_FORMAT(week_date, '%b-%Y')) AS displayDate,
    CASE
        WHEN COUNT(*) > 2
        AND SUM(
            CASE
                WHEN skills_retained IS NOT NULL
                AND hours IS NOT NULL THEN 1
                ELSE 0
            END
        ) >= 3 THEN ROUND(SUM(skills_retained) / SUM(hours), 2)
        ELSE NULL
    END AS skill_rate
FROM
    view_cumulative_graph_data
WHERE
    status = 1
GROUP BY
    client_id,
    DATE_FORMAT(week_date, '%m-%Y')
ORDER BY
    client_id,
    sortDate;

/*view_clients_doi_monthly_rate*/
CREATE VIEW view_clients_doi_monthly_rate AS
SELECT
    client_id,
    MAX(week_date) AS sortDate,
    MAX(DATE_FORMAT(week_date, '%Y-%m')) AS months,
    MAX(DATE_FORMAT(week_date, '%b-%Y')) AS displayDate,
    CASE
        WHEN COUNT(*) > 2
        AND SUM(
            CASE
                WHEN doi IS NOT NULL
                AND hours IS NOT NULL THEN 1
                ELSE 0
            END
        ) >= 3 THEN ROUND(SUM(doi) / SUM(hours), 2)
        ELSE NULL
    END AS doi_rate
FROM
    view_cumulative_graph_data
WHERE
    status = 1
GROUP BY
    client_id,
    DATE_FORMAT(week_date, '%m-%Y')
ORDER BY
    client_id,
    sortDate;

/*view_clients_skills_target_rate*/
CREATE VIEW view_clients_skills_target_rate AS
select
    `c`.`id` AS `id`,
    `c`.`mrn` AS `mrn`,
    `c`.`internal_mrn` AS `internal_mrn`,
    `c`.`first_name` AS `first_name`,
    `c`.`last_name` AS `last_name`,
    `c`.`status` AS `status`,
    round(
        (
            sum(`cgd`.`skills_retained`) / sum(`cgd`.`hours`)
        ),
        2
    ) AS `target_rate`
from
    (
        (
            `clients` `c`
            left join `client_graph_target_month` `cgtm` on((`c`.`id` = `cgtm`.`client_id`))
        )
        left join `view_cumulative_graph_data` `cgd` on(
            (
                (`cgtm`.`client_id` = `cgd`.`client_id`)
                and (`cgtm`.`graph_type` = 'Skills')
                and (`cgd`.`status` = 1)
                and (
                    date_format(`cgd`.`week_date`, '%Y-%m') = date_format(`cgtm`.`t_date`, '%Y-%m')
                )
            )
        )
    )
group by
    `c`.`id`,
    `c`.`mrn`,
    `c`.`internal_mrn`,
    `c`.`first_name`,
    `c`.`last_name`,
    `c`.`status`;

/*view_clients_doi_target_rate*/
CREATE VIEW view_clients_doi_target_rate AS
select
    `c`.`id` AS `id`,
    `c`.`mrn` AS `mrn`,
    `c`.`internal_mrn` AS `internal_mrn`,
    `c`.`first_name` AS `first_name`,
    `c`.`last_name` AS `last_name`,
    `c`.`status` AS `status`,
    round(
        (
            sum(`cgd`.`doi`) / sum(`cgd`.`hours`)
        ),
        2
    ) AS `target_rate`
from
    (
        (
            `clients` `c`
            left join `client_graph_target_month` `cgtm` on((`c`.`id` = `cgtm`.`client_id`))
        )
        left join `view_cumulative_graph_data` `cgd` on(
            (
                (`cgtm`.`client_id` = `cgd`.`client_id`)
                and (`cgtm`.`graph_type` = 'DOI')
                and (`cgd`.`status` = 1)
                and (
                    date_format(`cgd`.`week_date`, '%Y-%m') = date_format(`cgtm`.`t_date`, '%Y-%m')
                )
            )
        )
    )
group by
    `c`.`id`,
    `c`.`mrn`,
    `c`.`internal_mrn`,
    `c`.`first_name`,
    `c`.`last_name`,
    `c`.`status`;

/*view_clients_all_targets*/
CREATE VIEW view_clients_all_targets AS
SELECT
    doi.id,
    doi.mrn,
    doi.internal_mrn,
    doi.first_name,
    doi.last_name,
    doi.status,
    doi.target_rate AS doi_target_rate,
    skills.target_rate AS skills_target_rate
FROM
    view_clients_doi_target_rate doi
    JOIN view_clients_skills_target_rate skills ON doi.id = skills.id;

/*view_clients_met_target_month_vise*/
CREATE VIEW view_clients_met_target_month_vise AS
SELECT
    skills.client_id,
    skills.months,
    targets.skills_target_rate AS skills_target,
    skills.skill_rate,
    targets.doi_target_rate AS doi_target,
    doi.doi_rate,
    CASE
        WHEN (
            targets.skills_target_rate IS NULL
            OR skills.skill_rate IS NULL
        )
        AND (
            targets.doi_target_rate IS NULL
            OR doi.doi_rate IS NULL
        ) THEN NULL
        WHEN skills.skill_rate >= targets.skills_target_rate
        OR doi.doi_rate >= targets.doi_target_rate THEN 1
        ELSE 0
    END AS target_status
FROM
    view_clients_skills_monthly_rate skills
    LEFT JOIN view_clients_doi_monthly_rate doi ON skills.client_id = doi.client_id
    AND skills.months = doi.months
    LEFT JOIN view_clients_all_targets targets ON skills.client_id = targets.id;

/*view_clients_target_start_month*/
CREATE VIEW view_clients_target_start_month AS
SELECT
    client_id,
    MAX(
        CASE
            WHEN graph_type = 'Skills' THEN t_date
        END
    ) AS max_date_skills,
    MAX(
        CASE
            WHEN graph_type = 'DOI' THEN t_date
        END
    ) AS max_date_doi,
    CASE
        WHEN MAX(
            CASE
                WHEN graph_type = 'Skills' THEN t_date
            END
        ) IS NOT NULL
        AND MAX(
            CASE
                WHEN graph_type = 'DOI' THEN t_date
            END
        ) IS NOT NULL THEN DATE_FORMAT(
            LEAST(
                MAX(
                    CASE
                        WHEN graph_type = 'Skills' THEN t_date
                    END
                ),
                MAX(
                    CASE
                        WHEN graph_type = 'DOI' THEN t_date
                    END
                )
            ),
            '%Y-%m'
        )
        WHEN MAX(
            CASE
                WHEN graph_type = 'Skills' THEN t_date
            END
        ) IS NOT NULL
        AND MAX(
            CASE
                WHEN graph_type = 'DOI' THEN t_date
            END
        ) IS NULL THEN DATE_FORMAT(
            MAX(
                CASE
                    WHEN graph_type = 'Skills' THEN t_date
                END
            ),
            '%Y-%m'
        )
        WHEN MAX(
            CASE
                WHEN graph_type = 'Skills' THEN t_date
            END
        ) IS NULL
        AND MAX(
            CASE
                WHEN graph_type = 'DOI' THEN t_date
            END
        ) IS NOT NULL THEN DATE_FORMAT(
            MAX(
                CASE
                    WHEN graph_type = 'DOI' THEN t_date
                END
            ),
            '%Y-%m'
        )
        ELSE NULL
    END AS start_month
FROM
    client_graph_target_month
WHERE
    graph_type IN ('Skills', 'DOI')
GROUP BY
    client_id;

/*view_mands_totals_and_variety_by_session*/
CREATE VIEW view_mands_totals_and_variety_by_session AS
SELECT
    msd.session_id,
    msd.client_id,
    COUNT(*) AS total_mands,
    SUM(
        CASE
            WHEN msd.is_peer_manding = 1 THEN 1
            ELSE 0
        END
    ) AS total_peer_mands,
    SUM(
        CASE
            WHEN msd.is_eye_contact = 1 THEN 1
            ELSE 0
        END
    ) AS total_eye_contact_mands,
    COUNT(DISTINCT msd.reinforcer_input) AS variety_of_mands,
    COALESCE(vld.hours, 0) AS hours,
    vld.total_duration_of_mands AS total_duration_formatted,
    -- just selecting existing field
    CASE
        WHEN vld.hours > 0 THEN ROUND((COUNT(*) / (vld.hours * 60)), 2)
        ELSE NULL
    END AS frequency_of_mands_per_minute,
    CASE
        WHEN vld.hours > 0 THEN ROUND((
            SUM(
                CASE
                    WHEN msd.is_peer_manding = 1 THEN 1
                    ELSE 0
                END
            ) / (vld.hours * 60)
        ), 2)
        ELSE NULL
    END AS frequency_of_peer_mands_per_minute,
    CASE
        WHEN vld.hours > 0 THEN ROUND((
            SUM(
                CASE
                    WHEN msd.is_eye_contact = 1 THEN 1
                    ELSE 0
                END
            ) / (vld.hours * 60)
        ), 2)
        ELSE NULL
    END AS frequency_of_eye_contact_mands_per_minute
FROM
    mands_session_data msd
    LEFT JOIN view_live_data_mands_duration_by_session vld ON msd.session_id = vld.session_id
GROUP BY
    msd.session_id,
    msd.client_id,
    vld.hours,
    vld.total_duration_of_mands;

/*view_mands_session_data_summary*/
CREATE VIEW view_mands_session_data_summary AS
SELECT
    msd.client_id,
    msd.session_date,
    COUNT(*) AS total_mands,
    COALESCE(
        ROUND(
            TIME_TO_SEC(mdd.total_duration_of_mands) / 3600,
            2
        ),
        0
    ) AS total_duration,
    CASE
        WHEN COALESCE(TIME_TO_SEC(mdd.total_duration_of_mands), 0) > 0 THEN ROUND(
            COUNT(*) / (TIME_TO_SEC(mdd.total_duration_of_mands) / 60),
            2
        )
        ELSE NULL
    END AS frequency_of_mands_per_minute,
    SUM(
        CASE
            WHEN msd.is_peer_manding = 1 THEN 1
            ELSE 0
        END
    ) AS total_peer_mands,
    SUM(
        CASE
            WHEN msd.is_eye_contact = 1 THEN 1
            ELSE 0
        END
    ) AS total_eye_contact_mands,
    CASE
        WHEN COALESCE(TIME_TO_SEC(mdd.total_duration_of_mands), 0) > 0 THEN ROUND(
            SUM(
                CASE
                    WHEN msd.is_peer_manding = 1 THEN 1
                    ELSE 0
                END
            ) / (TIME_TO_SEC(mdd.total_duration_of_mands) / 60),
            2
        )
        ELSE NULL
    END AS frequency_of_peer_mands_per_minute,
    CASE
        WHEN COALESCE(TIME_TO_SEC(mdd.total_duration_of_mands), 0) > 0 THEN ROUND(
            SUM(
                CASE
                    WHEN msd.is_eye_contact = 1 THEN 1
                    ELSE 0
                END
            ) / (TIME_TO_SEC(mdd.total_duration_of_mands) / 60),
            2
        )
        ELSE NULL
    END AS frequency_of_eye_contact_mands_per_minute,
    COUNT(DISTINCT msd.reinforcer_input) AS variety_of_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 1 THEN 1
            ELSE 0
        END
    ) AS total_FPP_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 2 THEN 1
            ELSE 0
        END
    ) AS total_PPP_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 3 THEN 1
            ELSE 0
        END
    ) AS total_GP_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 4 THEN 1
            ELSE 0
        END
    ) AS total_V_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 5 THEN 1
            ELSE 0
        END
    ) AS total_IV_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 6 THEN 1
            ELSE 0
        END
    ) AS total_Item_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 7 THEN 1
            ELSE 0
        END
    ) AS total_MO_mands,
    SUM(
        CASE
            WHEN msd.prompt_level = 8 THEN 1
            ELSE 0
        END
    ) AS total_TMO_mands,
    SUM(
        CASE
            WHEN msd.mands_error IN (2, 3, 4) THEN 1
            ELSE 0
        END
    ) AS total_mands_with_errors,
    SUM(
        CASE
            WHEN msd.mands_error = 2 THEN 1
            ELSE 0
        END
    ) AS total_mands_errors_s,
    SUM(
        CASE
            WHEN msd.mands_error = 3 THEN 1
            ELSE 0
        END
    ) AS total_mands_errors_r,
    SUM(
        CASE
            WHEN msd.mands_error = 4 THEN 1
            ELSE 0
        END
    ) AS total_mands_errors_ia,
    ROUND(
        SUM(
            CASE
                WHEN msd.mands_error = 2 THEN 1
                ELSE 0
            END
        ) / COUNT(*) * 100,
        2
    ) AS percentage_of_scrolled_mands,
    ROUND(
        SUM(
            CASE
                WHEN msd.mands_error = 3 THEN 1
                ELSE 0
            END
        ) / COUNT(*) * 100,
        2
    ) AS percentage_of_repetitive_mands,
    ROUND(
        SUM(
            CASE
                WHEN msd.mands_error = 4 THEN 1
                ELSE 0
            END
        ) / COUNT(*) * 100,
        2
    ) AS percentage_of_inappropriate_autoclitics,
    SUM(
        CASE
            WHEN msd.initial_attempt IN (2, 3, 4, 5) THEN 1
            ELSE 0
        END
    ) AS total_mands_with_initial_attempts,
    ROUND(
        SUM(
            CASE
                WHEN msd.initial_attempt = 2 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.initial_attempt IN (2, 3, 4, 5) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_SS_attempts,
    ROUND(
        SUM(
            CASE
                WHEN msd.initial_attempt = 3 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.initial_attempt IN (2, 3, 4, 5) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_WA_attempts,
    ROUND(
        SUM(
            CASE
                WHEN msd.initial_attempt = 4 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.initial_attempt IN (2, 3, 4, 5) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_IW_attempts,
    ROUND(
        SUM(
            CASE
                WHEN msd.initial_attempt = 5 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.initial_attempt IN (2, 3, 4, 5) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_AF_attempts,
    SUM(
        CASE
            WHEN msd.comparison_prompt_delay IN (1, 2, 3) THEN 1
            ELSE 0
        END
    ) AS total_trials_with_prompt_delay,
    ROUND(
        SUM(
            CASE
                WHEN msd.comparison_prompt_delay = 2 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.comparison_prompt_delay IN (1, 2, 3) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_remained_with_prompt_delay,
    ROUND(
        SUM(
            CASE
                WHEN msd.comparison_prompt_delay = 3 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.comparison_prompt_delay IN (1, 2, 3) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_improved_with_prompt_delay,
    ROUND(
        SUM(
            CASE
                WHEN msd.comparison_prompt_delay = 1 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.comparison_prompt_delay IN (1, 2, 3) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_worsened_with_prompt_delay,
    SUM(
        CASE
            WHEN msd.comparison_echoic_trial IN (1, 2, 3) THEN 1
            ELSE 0
        END
    ) AS total_trials_with_echoic_trials,
    ROUND(
        SUM(
            CASE
                WHEN msd.comparison_echoic_trial = 2 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.comparison_echoic_trial IN (1, 2, 3) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_remained_with_echoic_trials,
    ROUND(
        SUM(
            CASE
                WHEN msd.comparison_echoic_trial = 3 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.comparison_echoic_trial IN (1, 2, 3) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_improved_with_echoic_trials,
    ROUND(
        SUM(
            CASE
                WHEN msd.comparison_echoic_trial = 1 THEN 1
                ELSE 0
            END
        ) / SUM(
            CASE
                WHEN msd.comparison_echoic_trial IN (1, 2, 3) THEN 1
                ELSE 0
            END
        ) * 100,
        2
    ) AS percentage_of_worsened_with_echoic_trials
FROM
    mands_session_data AS msd
    LEFT JOIN view_live_data_mands_duration_by_date AS mdd ON msd.client_id = mdd.client_id
    AND msd.session_date = mdd.week_date
GROUP BY
    msd.client_id,
    msd.session_date;

/**********************view_client_goal_mastery_status*****************************/
CREATE VIEW view_client_goal_mastery_status AS
SELECT
    p.client_id,
    p.domain_id,
    p.goal_id,
    p.client_probe_set_id,
    p.target_count AS target_count_program,
    COALESCE(r.target_count, 0) AS target_count_retained,
    CASE
        WHEN p.target_count = COALESCE(r.target_count, 0) THEN TRUE
        ELSE FALSE
    END AS is_goal_mastered
FROM
    (
        SELECT
            cpt.client_id,
            cpg.domain_id,
            cpt.goal_id,
            cps.id AS client_probe_set_id,
            COUNT(cpt.id) AS target_count
        FROM
            client_program_targets AS cpt
            JOIN client_program_goals AS cpg ON cpt.goal_id = cpg.id
            JOIN client_probe_set AS cps ON cps.client_id = cpt.client_id
            AND cps.goal_id = cpt.goal_id
        WHERE
            cps.is_active = 1
        GROUP BY
            cpt.client_id,
            cpg.domain_id,
            cpt.goal_id,
            cps.id
    ) AS p
    LEFT JOIN (
        SELECT
            client_id,
            domain_id,
            goal_id,
            client_probe_set_id,
            COUNT(target_id) AS target_count
        FROM
            client_program_targets_retained
        GROUP BY
            client_id,
            domain_id,
            goal_id,
            client_probe_set_id
    ) AS r ON p.client_id = r.client_id
    AND p.domain_id = r.domain_id
    AND p.goal_id = r.goal_id
    AND p.client_probe_set_id = r.client_probe_set_id;

CREATE
OR REPLACE VIEW view_target_stimulus_step_summary AS
SELECT
    cts.target_id,
    COUNT(cts.id) AS step_count,
    ctc.method AS chaining_method,
    ctc.rule_override
FROM
    client_target_stimulus_steps cts
    LEFT JOIN client_target_stimulus_chains ctc ON ctc.target_id = cts.target_id
GROUP BY
    cts.target_id,
    ctc.method,
    ctc.rule_override;

/**********************view_client_target_progress*****************************/
CREATE
OR REPLACE VIEW view_client_target_progress AS
SELECT
    c.id AS client_id,
    c.internal_mrn,
    COALESCE(p.introduced, 0) AS introduced,
    COALESCE(r.retained, 0) AS retained,
    CASE
        WHEN COALESCE(p.introduced, 0) = 0 THEN 0
        ELSE ROUND(
            (
                COALESCE(r.retained, 0) / COALESCE(p.introduced, 0)
            ) * 100,
            2
        )
    END AS percentage
FROM
    clients c
    LEFT JOIN (
        SELECT
            client_id,
            COUNT(DISTINCT target_id) AS introduced
        FROM
            daily_session_data_processed
        GROUP BY
            client_id
    ) p ON p.client_id = c.id
    LEFT JOIN (
        SELECT
            client_id,
            COUNT(DISTINCT target_id) AS retained
        FROM
            client_program_targets_retained
        GROUP BY
            client_id
    ) r ON r.client_id = c.id;
/*******view_client_target_program_change_summary*****************************/
CREATE OR REPLACE VIEW view_client_target_program_change_summary AS
SELECT
    a.client_id AS client_id,
    a.target_id AS target_id,
    a.client_probe_set_id AS client_probe_set_id,
    COUNT(DISTINCT a.id) AS program_alert_count,
    MAX(a.session_date) AS last_alert_date,
    COUNT(DISTINCT c.id) AS program_change_count,
    MAX(c.session_date) AS last_change_date
FROM client_program_change_alert AS a
LEFT JOIN client_program_change AS c
    ON c.alert_id = a.id
GROUP BY
    a.client_id,
    a.target_id,
    a.client_probe_set_id;