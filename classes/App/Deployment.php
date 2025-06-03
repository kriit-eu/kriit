<?php namespace App;

class Deployment
{

    public static function create()
    {
        try {
            // Extract commit meta data
            $gitOutput = trim(exec('git log --oneline --format=%h:::%cn:::%ci:::%s -n1 HEAD 2>&1'));

            // Check if git command was successful
            if (empty($gitOutput) || strpos($gitOutput, 'fatal:') !== false || strpos($gitOutput, 'not a git repository') !== false) {
                // Fallback values when git is not available or fails
                $sha = 'unknown';
                $author = 'unknown';
                $commit_date = date('Y-m-d H:i:s');
                $message = 'No git repository';
            } else {
                // Parse git output
                $parts = explode(':::', $gitOutput, 4); // Limit to 4 parts in case message contains :::

                if (count($parts) >= 4) {
                    [$sha, $author, $commit_date, $message] = $parts;
                } else {
                    // Fallback if parsing fails
                    $sha = $parts[0] ?? 'unknown';
                    $author = $parts[1] ?? 'unknown';
                    $commit_date = $parts[2] ?? date('Y-m-d H:i:s');
                    $message = $parts[3] ?? 'Parse error';
                }
            }

            // Insert new deployment to database
            Db::insert('deployments',[
                'deploymentCommitDate'=>substr($commit_date, 0,19),
                'deploymentDate'=>date('Y-m-d H:i:s'),
                'deploymentCommitMessage' => substr($message, 0, 19),
                'deploymentCommitSha'=>$sha,
                'deploymentCommitAuthor'=>$author
            ]);

        } catch (\Exception $e) {
            // Log error and insert fallback deployment record
            error_log("Deployment creation failed: " . $e->getMessage());

            Db::insert('deployments',[
                'deploymentCommitDate'=>date('Y-m-d H:i:s'),
                'deploymentDate'=>date('Y-m-d H:i:s'),
                'deploymentCommitMessage' => 'Error occurred',
                'deploymentCommitSha'=>'error',
                'deploymentCommitAuthor'=>'system'
            ]);
        }
    }
}
