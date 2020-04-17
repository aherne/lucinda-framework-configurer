<?php
/**
 * Extension of BasicLoginThrottler that uses an SQL database as storage medium
 */
class SqlLoginThrottler extends \Lucinda\Framework\AbstractLoginThrottler
{
    private $found;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\Form\LoginThrottler::setCurrentStatus()
     */
    protected function setCurrentStatus(): void
    {
        $row = SQL("SELECT attempts, penalty_expiration FROM user_logins WHERE ip=:ip AND username=:username", array(
            ":ip"=>$this->request->getIpAddress(),
            ":username"=>$this->userName
        ))->toRow();
        if (!empty($row)) {
            $this->attempts = $row["attempts"];
            $this->penaltyExpiration = $row["penalty_expiration"];
            $this->found = true;
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\AbstractLoginThrottler::persist()
     */
    protected function persist(): void
    {
        if (!$this->found) {
            SQL("INSERT INTO user_logins (ip, username, attempts, penalty_expiration) VALUES (:ip, :username, :attempts, :penalty_expiration)", array(
                ":ip"=>$this->request->getIpAddress(),
                ":username"=>$this->userName,
                ":attempts"=>$this->attempts,
                ":penalty_expiration"=>$this->penaltyExpiration
            ));
        } else {
            SQL("UPDATE user_logins SET attempts=:attempts, penalty_expiration=:penalty_expiration WHERE ip=:ip AND username=:username", array(
                ":ip"=>$this->request->getIpAddress(),
                ":username"=>$this->userName,
                ":attempts"=>$this->attempts,
                ":penalty_expiration"=>$this->penaltyExpiration
            ));
        }
    }
}
