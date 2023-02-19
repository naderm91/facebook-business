<?php
$campaigns = getAllCampaigns(
    $_ENV['APP_ID'],
    $_ENV['APP_SECRET'],
    $_SESSION['fb_business']['access_token'],
    $_SESSION['fb_business']['act_account_id'],
);
echo "
<table>
        <thead>
            <tr style='background: #ddd;'>
                <td>id</td>
                <td>name</td>
                <td>objective</td>
                <td>bid_strategy</td>
                <td>pacing_type</td>
                <td>lifetime_budget</td>
                <td>created_time</td>
                <td>effective_status</td>
                <td>buying_type</td>
                <td>start_time</td>
                <td>stop_time</td>
            </tr>
        </thead>
        <tbody>
            ";
if (count($campaigns)) foreach ($campaigns as $campaign) {
    echo "<tr>
        <td style='background: #eee;'>#{$campaign['id']}</td>
        <td>{$campaign['name']}</td>
        <td style='background: #eee;'>{$campaign['objective']}</td>
        <td>{$campaign['bid_strategy']}</td>
        <td style='background: #eee;'>" . json_encode($campaign['pacing_type']) . "</td>
        <td>{$campaign['lifetime_budget']}</td>
        <td style='background: #eee;'>" . date('d/M/Y h:i:s', strtotime($campaign['created_time'])) . "</td>
        <td>{$campaign['effective_status']}</td>
        <td style='background: #eee;'>{$campaign['buying_type']}</td>
        <td>" . date('d/M/Y h:i:s', strtotime($campaign['start_time'])) . "</td>
        <td style='background: #eee;'>" . ($campaign['stop_time'] ?  date('d/M/Y h:i:s', strtotime($campaign['stop_time'])) : 'NA') . "</td>
    </tr>";
}
else {
    echo "<tr><td colspan='7'>No data.</td></tr>";
}
echo "
        </tbody>
    </table>
";
