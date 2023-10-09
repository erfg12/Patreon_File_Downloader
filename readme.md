# ABOUT
If you need to provide a file downloader, and use Patreon for your payment subscriptions.

# PHP Server Setup

1. Use [Composer](https://getcomposer.org/) to install `patreon/patreon`.
2. [Register a Patreon client](https://www.patreon.com/portal/registration/register-clients) for API keys.
3. Open all PHP files and look for replacement strings.
4. Place files that will be downloaded in the `scripts/files` diretory.

# C# Client Example:

- Logging in, and letting user get their token:
```CSharp
Process.Start("https://(MY_DOMAIN_HERE)/patreon.php");
```

- Check token given by user for valid subscription:
```CSharp
string UsersPatreonToken = "USER_PROVIDED_TOKEN";
var url = "https://(MY_DOMAIN_HERE)/patreon.php?my_token=" + UsersPatreonToken;
var client = new WebClient();

using (var stream = client.OpenRead(url))
{
    using (var reader = new StreamReader(stream))
    {
        patreon_status_label.Text = reader.ReadToEnd();
        if (patreon_status_label.Text.Contains("Thank you for being a Patron"))
        {
            using (var stream = wClient.OpenRead("https://(MY_DOMAIN_HERE)/scripts/files.php"))
            {
                string[] words = reader.ReadLine().Split(';');

                foreach (string word in words)
                {
                    total++;
                }
                int t = 1;

                //step 1 : download files. This takes milliseconds
                foreach (string word in words)
                {
                    if (!word.Contains(','))
                        continue;
                    t++;

                    string[] s = word.Split(',');
                    string dlURL = "https://(MY_DOMAIN_HERE)/scripts/download.php?my_token=" + UsersPatreonToken + "&f=" + s[0].Replace('\\', '/');
                    Uri uri = new Uri(dlURL);

                    if (!Directory.Exists(Path.GetDirectoryName(pm.DLpath + s[0])))
                        Directory.CreateDirectory(Path.GetDirectoryName(pm.DLpath + s[0]));

                    if (!File.Exists(pm.DLpath + s[0]) 
                        || DateTime.Compare(File.GetLastWriteTime(pm.DLpath + s[0]), Convert.ToDateTime(s[1])) < 0)
                    {
                        using (WebClient webC = new WebClient())
                        {
                            try
                            {
                                if (s[0] != null && s[0] != "")
                                     webC.DownloadFile(uri, pm.DLpath + s[0]);
                            }
                            catch
                            {
                                // something went wrong
                            }
                        }
                        if (word.Contains(','))
                            updateTimeStamp.Add(s[0], s[1]);
                    }
                }
            }
        }
        else if (patreon_status_label.Text.Contains("No Patreon Access Token given.") || patreon_status_label.Text.Contains("ERROR: Authentication token not good."))
        {
            // something went wrong
        }
    }
}
```